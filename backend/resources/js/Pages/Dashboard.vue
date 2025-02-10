<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, } from '@inertiajs/vue3';
import { defineProps, ref } from 'vue';
import Modal from '@/Components/Modal.vue';


defineProps({
    posts: {
        type: Object,
        default: () => ({
            data: [], // 空の配列をデフォルトに
            prev_page_url: null,
            next_page_url: null,
        })
    }
});

// フォームの管理
const form = useForm({
    title: '',
    body: '',
});

// 投稿の処理
const submit = () => {
    form.post(route('posts.store'), {
        onSuccess: () => form.reset(),
    });
};

const goToPage = (url) => {
    if (url) {
        router.get(url);
    };
};

const isModalOpen = ref(false);
const selectedPost = ref(null);

const openModal = (post) => {
    // console.log("post has clicked",post);
    selectedPost.value = post;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    selectedPost.value = null;
};

</script>

<template>
    <div>
        <Head title="Dashboard" />

        <AuthenticatedLayout>
            <template #header>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">投稿一覧</h2>
            </template>

            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <!-- 投稿フォーム -->
                    <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">新しい投稿</h3>
                        <form @submit.prevent="submit">
                            <div class="mb-4">
                                <label for="title" class="block text-sm font-medium text-gray-700">タイトル</label>
                                <input
                                    id="title"
                                    v-model="form.title"
                                    type="text"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    required
                                />
                            </div>

                            <div class="mb-4">
                                <label for="body" class="block text-sm font-medium text-gray-700">本文</label>
                                <textarea
                                    id="body"
                                    v-model="form.body"
                                    rows="3"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    required
                                ></textarea>
                            </div>

                            <div class="flex justify-end">
                                <button
                                    type="submit"
                                    class="bg-blue-500 text-white px-4 py-2 rounded-md shadow hover:bg-blue-600 transition disabled:opacity-50"
                                    :disabled="form.processing"
                                >
                                    投稿する
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- 投稿一覧 -->
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">投稿一覧</h3>
                        <div v-if="posts.length === 0" class="text-gray-500">まだ投稿がありません。</div>
                        <div v-else>
                            <div 
                                v-for="post in posts.data" 
                                :key="post?.id" 
                                class="border-b border-gray-300 py-4 cursor-pointer hover:bg-gray-100 transition"
                                @click="openModal(post)"
                            >
                                <h4 class="text-md font-semibold">{{ post.title }}</h4>
                                <p class="text-sm text-gray-700">{{ post.body }}</p>
                                <p class="text-xs text-gray-500 mt-2">投稿者: {{ post?.user ? post.user.name : '不明なユーザ' }} | {{ new Date(post?.created_at).toLocaleString() }}</p>
                            </div>

                            <!-- ページネーション -->
                            <div class="flex justify-center space-x-2 mt-4">
                                <!-- 前のページがあれば表示 -->
                                <button v-if="posts.prev_page_url" @click="goToPage(posts.prev_page_url)" class="bg-gray-200 px-3 py-2 rounded">
                                    ← 前のページ
                                </button>

                                <!-- 次のページがあれば表示 -->
                                <button v-if="posts.next_page_url" @click="goToPage(posts.next_page_url)" class="bg-gray-200 px-3 py-2 rounded">
                                    次のページ →
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 投稿の詳細表示 -->
            <Modal :show="isModalOpen" @close="closeModal">
                <div class="p-6">
                    <h2 class="text-xl font-semibold mb-4">{{ selectedPost?.title }}</h2>
                    <p class="text-gray-700">{{ selectedPost?.body }}</p>
                    <p class="text-xs text-gray-500 mt-4">投稿者: {{ selectedPost?.user ? selectedPost.user.name : '不明なユーザ' }} | {{ new Date(selectedPost?.created_at).toLocaleString() }}</p>
                    <div class="mt-4 text-right">
                        <button @click="closeModal" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-700">
                            閉じる
                        </button>
                    </div>
                </div>
            </Modal>
        </AuthenticatedLayout>
    </div>
</template>