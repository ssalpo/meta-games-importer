import Alpine from 'alpinejs';

Alpine.data('productForm', () => ({
    isGeneratingEnglish: false,
    generationError: '',

    async generateEnglishCopy() {
        this.generationError = '';

        const productTitle = document.getElementById('deepseek_game_title')?.value?.trim()
            || document.getElementById('title_ru')?.value?.replace('для Meta / Oculus Quest', '')?.trim()
            || '';
        const generationPromptId = document.getElementById('generation_prompt_id')?.value ?? '';
        const instructions = document.getElementById('deepseek_instructions')?.value?.trim() ?? '';

        if (!productTitle) {
            this.generationError = 'Введите название продукта для генерации.';
            return;
        }

        if (!generationPromptId) {
            this.generationError = 'Выберите промпт для генерации.';
            return;
        }

        this.isGeneratingEnglish = true;

        try {
            const response = await fetch('/products/generate-english-copy', {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
                body: JSON.stringify({
                    generation_prompt_id: generationPromptId,
                    product_title: productTitle,
                    instructions,
                }),
            });

            const responseText = await response.text();
            let payload = {};

            try {
                payload = responseText ? JSON.parse(responseText) : {};
            } catch (error) {
                payload = {
                    message: responseText || 'Сервер вернул пустой или некорректный ответ.',
                };
            }

            if (!response.ok) {
                this.generationError = payload.message ?? 'Не удалось получить ответ DeepSeek.';
                return;
            }

            document.getElementById('title_ru').value = payload.title_ru ?? '';
            document.getElementById('description_ru').value = payload.description_ru ?? '';
            document.getElementById('title_en').value = payload.title_en ?? '';
            document.getElementById('description_en').value = payload.description_en ?? '';
        } catch (error) {
            this.generationError = error?.message
                ? `Ошибка соединения с DeepSeek: ${error.message}`
                : 'Ошибка соединения с DeepSeek.';
        } finally {
            this.isGeneratingEnglish = false;
        }
    },
}));

window.Alpine = Alpine;
Alpine.start();
