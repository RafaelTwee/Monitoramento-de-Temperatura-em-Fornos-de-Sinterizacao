#include <WiFi.h>
#include <HTTPClient.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include "max6675.h"
#include <vector>
#include "credentials.h" // Incluindo o arquivo de credenciais

#define SCK 18
#define CS 5
#define SO 19
#define VRX A0
#define VRY A1
#define SW 4

// A definição de ssid, password e scriptURL foi movida para credentials.h
LiquidCrystal_I2C lcd(0x27, 16, 2);
MAX6675 thermocouple(SCK, CS, SO);

volatile bool buttonPressed = false; // Flag para interrupção do botão
volatile bool measuring = false;    // Flag para controle de medição
std::vector<double> temperatureData; // Armazena as leituras de temperatura

void IRAM_ATTR handleButtonPress() {
  buttonPressed = true; // Marca que o botão foi pressionado
}

void setup() {
  Serial.begin(9600);
  pinMode(SW, INPUT_PULLUP);
  attachInterrupt(digitalPinToInterrupt(SW), handleButtonPress, FALLING); // Configura a interrupção

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
}

void loop() {
  if (!measuring) {
    lcd.setCursor(0, 0);
    lcd.print("1.Iniciar Medicao");
  }

  if (buttonPressed) { // Verifica se o botão foi pressionado
    delay(200); // Debounce
    buttonPressed = false; // Reseta a flag

    if (!measuring) {
      startMeasurement();
    } else {
      stopMeasurement();
    }
  }

  if (measuring) {
    double tempC = thermocouple.readCelsius();
    temperatureData.push_back(tempC); // Armazena a leitura no vetor

    lcd.setCursor(0, 0);
    lcd.print("Temp: ");
    lcd.print(tempC);
    lcd.print(" C ");

    Serial.print("Temperatura: ");
    Serial.println(tempC);

    delay(1000); // Intervalo entre leituras
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
  temperatureData.clear(); // Limpa os dados anteriores
}

void stopMeasurement() {
  measuring = false;
  lcd.clear();
  lcd.print("Medicao finalizada");
  delay(2000);
  lcd.clear();

  sendToGoogleSheets(); // Envia os dados para a planilha.
}
void sendToGoogleSheets() {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    int totalTemperatures = temperatureData.size(); // Total de temperaturas a enviar

    lcd.clear();
    lcd.print("Enviando dados...");

    for (size_t i = 0; i < totalTemperatures; i++) {
      http.begin(scriptURL);
      http.addHeader("Content-Type", "application/json");

      // Cria um JSON com uma única temperatura
      String jsonPayload = "{\"temperatura\": " + String(temperatureData[i]) + "}";

      Serial.println("Enviando dados: " + jsonPayload); // Debug: Exibe o JSON no Serial

      int httpResponseCode = http.POST(jsonPayload);
      
      Serial.print("HTTP Response: ");
      Serial.println(httpResponseCode);
      
      http.end();

      // Atualiza a barra de progresso no LCD
      int progress = ((i + 1) * 16) / totalTemperatures; // Calcula o progresso (0 a 16)
      lcd.setCursor(0, 1);
      for (int j = 0; j < progress; j++) {
        lcd.write(255); // Caractere de bloco cheio (█)
      }

      delay(1000); // Intervalo entre envios para evitar sobrecarga
    }

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