#include <WiFi.h>
#include <HTTPClient.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include "max6675.h"
#include <vector>
#include "credentials.h" // Incluindo o arquivo de credenciais
#include <WiFiUdp.h>
#include <NTPClient.h>
#include <time.h>

#define SCK 18
#define CS 5
#define SO 19
#define VRX A0
#define VRY A1
#define SW 4

LiquidCrystal_I2C lcd(0x27, 16, 2);
MAX6675 thermocouple(SCK, CS, SO);

WiFiUDP udp;
NTPClient timeClient(udp, "pool.ntp.org", 0, 60000); // Sincroniza com o servidor NTP

volatile bool buttonPressed = false;  // Flag para interrupção do botão
volatile bool measuring = false;      // Flag para controle de medição
std::vector<std::vector<String>> temperatureData; // Lista de listas para armazenar as linhas de dados

unsigned long measurementStartTime = 0; // Tempo inicial do experimento
String experimentName = "";             // Nome do experimento gerado no início

void IRAM_ATTR handleButtonPress() {
  buttonPressed = true;
}

void setup() {
  Serial.begin(9600);
  pinMode(SW, INPUT_PULLUP);
  attachInterrupt(digitalPinToInterrupt(SW), handleButtonPress, FALLING);

  lcd.init();
  lcd.backlight();
  lcd.print("Conectando WiFi...");

  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println("Conectado!");
  lcd.clear();

  timeClient.begin();
}

void loop() {
  if (measuring) {
  double tempC = thermocouple.readCelsius();
  float elapsedSeconds = (millis() - measurementStartTime) / 1000.0;
  String currentDateTime = getCurrentDateTime();

  temperatureData.push_back({
    currentDateTime,
    String(elapsedSeconds, 1), // Mostra 1 casa decimal
    String(tempC),
    experimentName
  });

  lcd.setCursor(0, 0);
  lcd.print("Temp: ");
  lcd.print(tempC);
  lcd.print(" C ");

  Serial.print("Temperatura: ");
  Serial.println(tempC);

  delay(500);
  }
}

void startMeasurement() {
  lcd.clear();
  lcd.print("Iniciando em:");
  for (int i = 3; i > 0; i--) {
    lcd.setCursor(0, 1);
    lcd.print(i);
    delay(1000);
  }
  lcd.clear();

  measuring = true;
  temperatureData.clear();

  measurementStartTime = millis();
  String timestamp = getCurrentDateTime();
  experimentName = "Exp_" + timestamp;
}

void stopMeasurement() {
  measuring = false;
  lcd.clear();
  lcd.print("Medicao finalizada");
  delay(2000);
  lcd.clear();

  sendToGoogleSheets();
}

String getCurrentDateTime() {
  timeClient.update();
  time_t rawTime = timeClient.getEpochTime();
  struct tm *timeInfo = localtime(&rawTime);

  char buffer[20];
  sprintf(buffer, "%02d/%02d/%04d_%02d:%02d:%02d",
          timeInfo->tm_mday,
          timeInfo->tm_mon + 1,
          timeInfo->tm_year + 1900,
          timeInfo->tm_hour,
          timeInfo->tm_min,
          timeInfo->tm_sec);
  return String(buffer);
}

void sendToGoogleSheets() {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;

    lcd.clear();
    lcd.print("Enviando dados...");

    String jsonPayload = "[";

    for (size_t i = 0; i < temperatureData.size(); i++) {
      jsonPayload += "{";
      jsonPayload += "\"data_hora\": \"" + temperatureData[i][0] + "\",";
      jsonPayload += "\"tempo_decorrido\": \"" + temperatureData[i][1] + "\",";
      jsonPayload += "\"temperatura\": " + temperatureData[i][2] + ",";

      // Só envia o nome na primeira linha
      if (i == 0) {
        jsonPayload += "\"nome\": \"" + temperatureData[i][3] + "\"";
      } else {
        jsonPayload += "\"nome\": \"\"";
      }

      jsonPayload += "}";

      if (i < temperatureData.size() - 1) {
        jsonPayload += ",";
      }
    }

    jsonPayload += "]";

    http.begin(scriptURL);
    http.addHeader("Content-Type", "application/json");

    Serial.println("Enviando dados: " + jsonPayload);

    int httpResponseCode = http.POST(jsonPayload);

    Serial.print("HTTP Response: ");
    Serial.println(httpResponseCode);

    http.end();

    lcd.clear();
    lcd.print("Dados enviados!");
    delay(2000);
  } else {
    Serial.println("Falha na conexão WiFi");
    lcd.clear();
    lcd.print("Erro: Sem WiFi");
    delay(2000);
  }
}
