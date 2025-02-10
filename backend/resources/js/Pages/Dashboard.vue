<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue'; 
const form = useForm({
    title: '',
    body: '',
});

const submit = () => {
    form.post(route('posts.store'), {
        onSuccess: () => form.reset(),
    });
};

</script>

```vue
<template>
    <div>
        <Head title="Dashboard" />

        <AuthenticatedLayout>
            <template #header>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
            </template>

            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <!-- 投稿フォームを作成 -->
                    <div class= "bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                        <h3 class="font-semibold text-lg text-gray-800 mb-4">投稿フォーム</h3>

                        <form @submit.prevent="submit">
                            <div class="mb-4">
                                <label for="title" class="block text-sm font-medium text-gray-700">タイトル</label>
                                <input
                                    id="title"
                                    v-model="form.title"
                                    type="text" 
                                    class="mt-1 block w-full border-gray-300 shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500 rounded-md"
                                    required
                                />       
                            </div>

                            <!-- 本文入力 -->
                            <div class="mb-4">
                                <label for="body" class="block text-sm font-medium text-gray-700">本文</label>
                                <textarea
                                    id="body"
                                    v-model="form.body"
                                    class="mt-1 block w-full border-gray-300 shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500 rounded-md"
                                    required
                                ></textarea>
                            </div>

                            <!-- 投稿ボタン -->
                            <div class="flex justify-end">
                                <button
                                    type="submit"
                                    class="bg-blue-500 text-white px-4 py-2 rounded-md shadow hover:bg-blue-600 transition disabled:opacity-50"
                                    :disabled="isProcessing"
                                >
                                    投稿する
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- 投稿一覧 -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">今後ここに投稿を表示させていく</div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    </div>
</template>
