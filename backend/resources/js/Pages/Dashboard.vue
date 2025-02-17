<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { defineProps, ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import axios from 'axios';
import { computed } from 'vue';



const props = defineProps({
    posts: {
        type: Object,
        default: () => ({
            data: [],
            prev_page_url: null,
            next_page_url: null,
        })
    },
    auth: {
        type: Object,
        default: () => ({
            user: null,
        })
    }
});


// 投稿フォーム管理
const form = useForm({
    title: '',
    body: '',
});

// 投稿の処理
// const submit = async () => {
//     try {
//         const response = await axios.post(route('posts.store'), {
//             title: form.title,
//             body: form.body,
//         });

//         if (response.data.post) {
//             // 新しい投稿を `posts` に追加
//             posts.value.unshift(response.data.post);

//             // AI コメントがある場合、即座に反映
//             if (response.data.comment) {
//                 response.data.post.comments = [response.data.comment];
//             }
//         }

//         form.reset();
//     } catch (error) {
//         console.error("投稿の作成に失敗しました:", error);
//     }
// };

// const submit = async () => {
//     try {
//         const response = await axios.post(route('posts.store'), {
//             title: form.title,
//             body: form.body,
//         });

//         if (response.data.post) {
//             // `posts.value` に新しい投稿を追加
//             posts.value.unshift({
//                 ...response.data.post,
//                 comments: response.data.comment ? [response.data.comment] : []
//             });
//         }

//         form.reset();
//     } catch (error) {
//         console.error("投稿の作成に失敗しました:", error);
//     }
// };
const submit = async () => {
    try {
        const response = await axios.post(route('posts.store'), {
            title: form.title,
            body: form.body,
        });

        if (response.data.post) {
            // Vue のリアクティブシステムを機能させるため、直接変更せず `push()` を使う
            props.posts.data.unshift({
                ...response.data.post,
                comments: response.data.comment ? [response.data.comment] : []
            });
        }

        form.reset();
    } catch (error) {
        console.error("投稿の作成に失敗しました:", error);
    }
};
const goToPage = (url) => {
    if (url) {
        router.get(url);
    };
};

// モーダル管理
const isModalOpen = ref(false);
const selectedPost = ref(null);

const isCommentModalOpen = ref(false);
const selectedComment = ref(null);

// モーダルを開く
// const openModal = async (post) => {
//     try {
//         console.log("Fetching post data...");
//         const response = await axios.get(route('posts.show', { post: post.id }));
//         selectedPost.value = response.data.post;
//         isModalOpen.value = true;
//     } catch (error) {
//         console.error("コメントの取得に失敗しました:", error);
//     }
// };
const openModal = async (post) => {
    try {
        console.log("Fetching post data...");
        const response = await axios.get(route('posts.show', { post: post.id }));

        // コメントが undefined の場合、空配列をセット
        selectedPost.value = {
            ...response.data.post,
            comments: response.data.post.comments ?? []  // ← 追加
        };

        isModalOpen.value = true;
    } catch (error) {
        console.error("コメントの取得に失敗しました:", error);
    }
};

const openCommentModal = (comment) => {
    console.log("comment has clicked");
    selectedComment.value = comment;
    isCommentModalOpen.value = true;
};

const closeCommentModal = () => {
    isCommentModalOpen.value = false;
    selectedComment.value = null;
};

// モーダルを閉じる
const closeModal = () => {
    isModalOpen.value = false;
    selectedPost.value = null;
    isEditing.value = false;
};

// コメントの削除
const deleteComment = async () => {
    if (!selectedComment.value) return;

    try {
        await axios.delete(route('comments.destroy', { comment: selectedComment.value.id }));

        // 選択中の投稿のコメント一覧から削除
        selectedPost.value.comments = selectedPost.value.comments.filter(c => c.id !== selectedComment.value.id);

        // モーダルを閉じる
        closeCommentModal();
    } catch (error) {
        console.error("コメントの削除に失敗しました:", error);
    }
};

const deletePost = (post) => {
    console.log(selectedPost.value.id);
    if (confirm('ほんまに消すでー？')) {
        router.delete(route('posts.destroy', selectedPost.value.id), {
            onSuccess: () => closeModal(),
        });
    }
};

const startEdit = () => {
    isEditing.value = true;
    editForm.title = selectedPost.value.title;
    editForm.body = selectedPost.value.body;
};

const updatePost = () => {
    router.put(route('posts.update', selectedPost.value.id), editForm, {
        onSuccess: () => {
            selectedPost.value.title = editForm.title;
            selectedPost.value.body = editForm.body;
            isEditing.value = false;
        },
    });
};

// 編集管理
const isEditing = ref(false);
const editForm = useForm({
    title: '',
    body: ''
});

// コメントフォーム管理
const commentForm = useForm({
    body: ''
});


// コメントの送信
const submitComment = async () => {
    if (!selectedPost.value) return;

    try {
        const response = await axios.post(route('comments.store', { post: selectedPost.value.id }), {
            body: commentForm.body,
        });

        if (response.data.comment) {
            // コメント一覧に追加
            selectedPost.value.comments.push({
                id: response.data.comment.id,
                body: response.data.comment.body,
                user: response.data.comment.user || { name: auth.user.name }, // ユーザー情報
                user_id: response.data.comment.user_id || auth.user.id

            });

            // フォームをリセット
            commentForm.reset();
        } else {
            console.error("コメントデータが返されませんでした");
        }
    } catch (error) {
        console.error("コメントの送信に失敗しました:", error);
    }
};

// コメントの削除の際にログインユーザがコメント主か判断
const isCommentOwner = computed(() => {
    return selectedComment.value?.user_id === auth.user?.id;
});

const page = usePage();
const auth = page.props.auth;
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
                        <form @submit.prevent="submit">
                            <div class="mb-4">
                                <label for="title" class="block text-sm font-medium text-gray-700">タイトル</label>
                                <input
                                    id="title"
                                    v-model="form.title"
                                    type="text"
                                    placeholder="いまどうしてる？"
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
                        <div v-if="posts.data.length === 0" class="text-gray-500">まだ投稿がありません。</div>
                        <div v-else>
                            <div 
                                v-for="post in posts.data" 
                                :key="post.id" 
                                class="border-b border-gray-300 py-4 cursor-pointer hover:bg-gray-100 transition"
                                @click="openModal(post)"
                            >
                                <h4 class="text-md font-semibold">{{ post.title }}</h4>
                                <p class="text-sm text-gray-700">{{ post.body }}</p>
                                <p class="text-xs text-gray-500 mt-2">
                                    投稿者: {{ post.user ? post.user.name : '不明なユーザ' }} | {{ new Date(post.created_at).toLocaleString() }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- ページネーション -->
                    <div class="flex justify-center space-x-2 mt-4">
                        <button v-if="posts.prev_page_url" @click="goToPage(posts.prev_page_url)" class="bg-gray-200 px-3 py-2 rounded">
                            ← 前のページ
                        </button>
                        <button v-if="posts.next_page_url" @click="goToPage(posts.next_page_url)" class="bg-gray-200 px-3 py-2 rounded">
                            次のページ →
                        </button>
                    </div>
                </div>
            </div>

            <!-- 投稿の詳細表示 (モーダル) -->
            <Modal :show="isModalOpen" @close="closeModal">
                <div class="p-6">
                    <h2 v-if="!isEditing" class="text-xl font-semibold mb-4">{{ selectedPost?.title }}</h2>
                    <p v-if="!isEditing" class="text-gray-700">{{ selectedPost?.body }}</p>
                    <!-- 編集フォーム -->
                    <div v-if="isEditing">
                        <label class="block text-sm font-medium text-gray-700">タイトル</label>
                        <input
                            v-model="editForm.title"
                            type="text"
                            class="w-full border rounded p-2 mb-2"
                        />
                        <label class="block text-sm font-medium text-gray-700">本文</label>
                        <textarea
                            v-model="editForm.body"
                            class="w-full border rounded p-2 mb-4"
                        ></textarea>
                        <button @click="updatePost" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">
                            更新
                        </button>
                        <button @click="isEditing = false" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-700">
                            キャンセル
                        </button>
                    </div>

                    <!-- 投稿者にのみ表示 -->
                    <div v-if="selectedPost?.user_id === auth?.user?.id && !isEditing" class="mt-4 flex justify-end space-x-2">
                        <button @click="startEdit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-yellow-700">
                            編集
                        </button>
                        <button @click="deletePost(selectedPost)" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-700">
                            削除
                        </button>
                    </div>

                    <p class="text-xs text-gray-500 mt-4">
                        投稿者: {{ selectedPost?.user ? selectedPost.user.name : '不明なユーザ' }} | {{ new Date(selectedPost?.created_at).toLocaleString() }}</p>

                    <!-- コメントエリア -->
                    <div class="mt-6 bg-gray-50 p-4 rounded">
                        <h3 class="text-md font-semibold">コメント</h3>

                        <!-- コメント入力フォーム -->
                        <form @submit.prevent="submitComment">
                            <textarea
                                v-model="commentForm.body"
                                class="w-full border rounded p-2 mt-2"
                                placeholder="コメントを入力..."
                                required
                            ></textarea>
                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded mt-2 hover:bg-blue-700">
                                送信
                            </button>
                        </form>

                        <!-- コメント一覧 -->
                        <div v-if="selectedPost?.comments?.length > 0" class="mt-4">
                            <div v-for="comment in [...selectedPost.comments].reverse()" :key="comment.id" 
                                class="border-b py-2"
                                @click="openCommentModal(comment)">
                                <p class="text-sm"><strong>{{ comment.user?.name ?? '不明なユーザ' }}</strong>: {{ comment.body }}</p>
                            </div>
                        </div>
                        <p v-else class="text-gray-500 mt-2">まだコメントがありません。</p>
                    </div>
                </div>
            </Modal>

            <!-- コメントの詳細表示 (モーダル) -->
            <Modal :show="isCommentModalOpen" @close="closeCommentModal">
                <div class="p-6">
                    <h2 class="text-lg font-semibold mb-4">コメント詳細</h2>
                    <p class="text-gray-700">{{ selectedComment?.body }}</p>
                    <p class="text-xs text-gray-500 mt-2">
                        投稿者: {{ selectedComment?.user?.name ?? '不明なユーザ' }}
                    </p>

                    <!-- 投稿者だけが削除できる -->
                    <div v-if="isCommentOwner" class="mt-4 flex justify-end">
                        <button @click="deleteComment" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-700">
                            削除
                        </button>
                    </div>

                    <div class="mt-4 text-right">
                        <button @click="closeCommentModal" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-700">
                            閉じる
                        </button>
                    </div>
                </div>
            </Modal>
<!-- 
                    <div class="mt-4 text-right">
                        <button @click="closeModal" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-700">
                            閉じる
                        </button>
                    </div>
                </div>
            </Modal> -->
        </AuthenticatedLayout>
    </div>
</template>