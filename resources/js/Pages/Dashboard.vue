<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, onMounted, nextTick, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    users: Object
});


const allUsers = ref([...props.users.data]);
const currentPage = ref(props.users.current_page);
const hasMorePages = ref(props.users.next_page_url !== null);
const isLoadingUsers = ref(false);

const usersListContainer = ref(null);

const page = usePage();
const currentUser = page.props.auth.user;

const selectedUser = ref(null);
const messages = ref([]);
const newMessage = ref('');
const messagesContainer = ref(null);

const errors = ref({});

watch(() => props.users, (newUsers) => {
    allUsers.value = [...newUsers.data];
    currentPage.value = newUsers.current_page;
    hasMorePages.value = newUsers.next_page_url !== null;
}, { deep: true });

const loadMoreUsers = async () => {
    if (isLoadingUsers.value || !hasMorePages.value) return;

    isLoadingUsers.value = true;
    try {
        const nextPage = currentPage.value + 1;
        const response = await axios.get(route('users.page'), {
            params: { page: nextPage }
        });

        allUsers.value = [...allUsers.value, ...response.data.data];
        currentPage.value = response.data.current_page;
        hasMorePages.value = response.data.next_page_url !== null;
    } catch (error) {
        console.error("Помилка підвантаження юзерів:", error);
    } finally {
        isLoadingUsers.value = false;
    }
};

const handleUserListScroll = (e) => {
    const element = e.target;
    if (element.scrollHeight - element.scrollTop <= element.clientHeight + 100) {
        loadMoreUsers();
    }
};


const selectUser = async (user) => {
    selectedUser.value = user;

    user.unread_count = 0;

    try {
        axios.post(route('messages.read', user.id));

        const response = await axios.get(route('messages.show', user.id));
        messages.value = response.data;
        scrollToBottom();
    } catch (error) {
        console.error("Error:", error);
    }
};

const sendMessage = async () => {
    if (!newMessage.value.trim() || !selectedUser.value) return;
    errors.value = {};

    try {
        const response = await axios.post(route('messages.store'), {
            receiver_id: selectedUser.value.id,
            content: newMessage.value
        });

        messages.value.push(response.data);
        newMessage.value = '';
        scrollToBottom();
    } catch (error) {
        console.error("Помилка відправки:", error);
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors;
        }
    }
};

onMounted(() => {
    window.Echo.private(`messenger.${currentUser.id}`)
        .listen('.MessageSent', (e) => {
            if (selectedUser.value && e.message.sender_id === selectedUser.value.id) {
                messages.value.push(e.message);
                axios.post(route('messages.read', selectedUser.value.id));
                scrollToBottom();
            } else {
                const sender = allUsers.value.find(u => u.id === e.message.sender_id);
                if (sender) {
                    sender.unread_count++;
                }
            }
        });
});

const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Chats
            </h2>
        </template>

        <div class="py-12 flex h-[calc(100vh-160px)]">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 w-96 flex flex-col">
                <div
                    ref="usersListContainer"
                    @scroll="handleUserListScroll"
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg flex-1 overflow-y-auto">
                    <div class="p-4 border-b border-gray-100">
                        <h3 class="text-lg font-medium text-gray-700">Chats</h3>
                    </div>

                    <div class="flex-1 overflow-y-auto divide-y divide-gray-100">
                        <div
                            v-for="user in allUsers"
                            :key="user.id"
                            @click="selectUser(user)"
                            :class="['flex items-center gap-4 p-4 cursor-pointer transition-colors',
                                selectedUser?.id === user.id ? 'bg-indigo-50' : 'hover:bg-gray-50']"
                        >
                            <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold shrink-0">
                                {{ user.name.charAt(0) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-semibold text-gray-900 truncate">{{ user.name }}</h4>
                                <p class="text-xs text-gray-500 truncate">{{ user.email }}</p>
                            </div>
                            <div v-if="user.unread_count > 0"
                                 class="bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded-full">
                                {{ user.unread_count }}
                            </div>
                        </div>

                        <div v-if="isLoadingUsers" class="p-4 text-center text-gray-500 text-xs italic">
                            Loading more users...
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex-1 flex flex-col bg-white shadow-sm sm:rounded-lg overflow-hidden border border-gray-100">
                <template v-if="selectedUser">
                    <div class="p-4 border-b border-gray-100 flex items-center gap-3 bg-gray-50/30">
                        <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-xs font-bold">
                            {{ selectedUser.name.charAt(0) }}
                        </div>
                        <span class="font-semibold text-gray-700">{{ selectedUser.name }}</span>
                    </div>

                    <div ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50/20">
                        <div v-for="msg in messages" :key="msg.id"
                             :class="['flex', msg.sender_id === currentUser.id ? 'justify-end' : 'justify-start']">
                            <div :class="['max-w-[70%] px-4 py-2 rounded-2xl text-sm shadow-sm',
                                        msg.sender_id === currentUser.id ? 'bg-indigo-600 text-white rounded-tr-none' : 'bg-white text-gray-800 border border-gray-200 rounded-tl-none']">
                                {{ msg.content }}
                                <div :class="['text-[10px] mt-1 opacity-70', msg.sender_id === currentUser.id ? 'text-right' : 'text-left']">
                                    {{ new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 border-t border-gray-100 bg-white">
                        <form @submit.prevent="sendMessage" class="flex gap-2">
                            <input
                                v-model="newMessage"
                                type="text"
                                maxlength="5000"
                                placeholder="Type a message..."
                                class="flex-1 border-gray-200 rounded-full focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                            />
                            <button
                                type="submit"
                                class="bg-indigo-600 text-white px-6 py-2 rounded-full hover:bg-indigo-700 transition-colors text-sm font-medium"
                            >
                                Send
                            </button>
                        </form>
                        <p v-if="errors.content" class="text-red-500 text-xs mt-1">{{ errors.content[0] }}</p>
                    </div>
                </template>

                <div v-else class="flex-1 flex flex-col items-center justify-center p-12 text-center">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Your Messages</h3>
                    <p class="text-gray-500 mt-1">Select a user to start a conversation</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
