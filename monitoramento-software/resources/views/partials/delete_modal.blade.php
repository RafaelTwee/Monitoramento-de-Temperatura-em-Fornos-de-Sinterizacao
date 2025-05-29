<div
  id="bulkDeleteModal"
  class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden"
>
  <div class="bg-white rounded-lg p-6 w-full max-w-md">
    <h3 class="text-lg font-semibold mb-4">Excluir Experimentos por Período</h3>
    <form method="POST" action="{{ route('experimentos.destroyRange') }}">
      @csrf
      <div class="mb-4">
        <label for="start_date" class="block text-sm font-medium">Data Início</label>
        <input
          type="date"
          id="start_date"
          name="start_date"
          required
          class="mt-1 block w-full border-gray-300 rounded-md"
        />
      </div>
      <div class="mb-4">
        <label for="end_date" class="block text-sm font-medium">Data Fim</label>
        <input
          type="date"
          id="end_date"
          name="end_date"
          required
          class="mt-1 block w-full border-gray-300 rounded-md"
        />
      </div>
      <div class="flex justify-end space-x-2">
        <button
          type="button"
          id="cancelBulkDelete"
          class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300"
        >Cancelar</button>
        <button
          type="submit"
          class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
        >Excluir</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
  // abre o modal
  document.getElementById('bulkDeleteBtn')
    .addEventListener('click', () => {
      document.getElementById('bulkDeleteModal')
              .classList.remove('hidden');
    });
  // fecha o modal
  document.getElementById('cancelBulkDelete')
    .addEventListener('click', () => {
      document.getElementById('bulkDeleteModal')
              .classList.add('hidden');
    });
</script>
@endpush
