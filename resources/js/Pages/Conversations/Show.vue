<script setup>
import ActionMessage from "@/Components/ActionMessage.vue";
import FormSection from "@/Components/FormSection.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextareaInput from "@/Components/TextareaInput.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import LoadingIcon from "@/Components/LoadingIcon.vue";
import { Link, useForm } from "@inertiajs/vue3";
import { computed, onMounted, ref } from "vue";

const props = defineProps({
    conversation: Object,
    models: Object,
    selectedModel: String,
});

const isAnswering = ref(false);
const chatBox = ref(null);

const isTokenLimitReached = computed(() => {
    const tokenLimit = props.selectedModel
        ? props.models[props.selectedModel].maxTokens
        : 0;
    return props.conversation.token_count >= tokenLimit * 0.8;
});

const form = useForm({
    _method: "POST",
    prompt: "",
});

const formAnswer = useForm({
    _method: "POST",
    model: props.selectedModel,
});

const send = async () => {
    if (isAnswering.value) {
        return;
    }

    form.post(
        route("messages.send", { conversationId: props.conversation.id }),
        {
            errorBag: "prompt",
            preserveScroll: true,
            onStart: () => {
                form.reset();
            },
            onSuccess: async () => {
                scrollChatBox();
                await answer();
            },
        }
    );
};

const answer = async () => {
    isAnswering.value = true;
    await wait(100);
    scrollChatBox();
    formAnswer.post(
        route("conversations.answer", {
            conversationId: props.conversation.id,
        }),
        {
            onBefore: () => {},
            preserveScroll: true,
            onSuccess: () => {
                isAnswering.value = false;
                scrollChatBox();
                updateTitle();
            },
            onError: () => {
                isAnswering.value = false;
            },
        }
    );
};

const scrollChatBox = () => {
    if (chatBox.value) {
        chatBox.value.scrollTop = chatBox.value.scrollHeight;
    }
};

onMounted(() => {
    scrollChatBox();
});

const formUpdateTitle = useForm({
    _method: "POST",
});

const isTitleUpdating = ref(false);

const updateTitle = () => {
    if (
        (props.conversation.messages.length > 1 &&
            props.conversation.messages.length < 7) ||
        (props.conversation.messages.length % 4 === 0 &&
            props.conversation.messages.length > 6)
    ) {
        isTitleUpdating.value = true;
        formUpdateTitle.post(
            route("conversations.updateTitle", {
                conversationId: props.conversation.id,
            }),
            {
                onSuccess: () => {
                    isTitleUpdating.value = false;
                },
                onError: () => {
                    isTitleUpdating.value = false;
                },
            }
        );
    }
};

const formUpdateSelectedModel = useForm({
    _method: "POST",
    model: props.selectedModel,
});

const updateSelectedModel = (value) => {
    formUpdateSelectedModel.model = value;
    formAnswer.model = value;
    formUpdateSelectedModel.post(
        route("models.select", { conversationId: props.conversation.id }),
        {
            preserveScroll: true,
            onSuccess: () => {},
        }
    );
};

const wait = async (ms) => new Promise((resolve) => setTimeout(resolve, ms));
</script>

<template>
    <AppLayout :title="`${conversation.title} - Conversations`">
        <template #header>
            <div class="flex justify-between space-x-6 items-center">
                <div>
                    <h2
                        class="flex space-x-2 items-baseline font-semibold text-xl text-gray-800 leading-tight"
                    >
                        <div>
                            {{ conversation.title }}
                        </div>
                        <div>
                            <LoadingIcon
                                v-if="isTitleUpdating"
                                class="-mb-1 w-5 h-5 animate-spin text-blue-900"
                            />
                        </div>
                    </h2>
                </div>
                <div class="">
                    <Link
                        :href="route('conversations.index')"
                        class="text-indigo-600 hover:text-indigo-900 transition"
                    >
                        Retour
                    </Link>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <FormSection @submitted="send">
                <template #title> Conversation </template>

                <template #description> Discutez avec l'assistant. </template>

                <template #form>
                    <div class="col-span-12">
                        <div>
                            <h3 class="text-lg font-semibold">Messages</h3>
                        </div>

                        <div class="">
                            <ul
                                class="bg-gray-100 p-4 mt-4 rounded-xl"
                                v-if="!conversation.messages.length"
                            >
                                <li>
                                    <p>Pas encore de message.</p>
                                </li>
                            </ul>
                            <ul
                                v-else
                                ref="chatBox"
                                class="bg-gray-100 p-4 mt-4 rounded-xl flex flex-col space-y-8 max-h-[50vh] overflow-y-auto"
                            >
                                <li
                                    class="max-w-[80%] p-4 rounded-lg"
                                    :class="{
                                        'self-end bg-green-100 text-right':
                                            message.role === 'user',
                                        'bg-indigo-100':
                                            message.role === 'assistant',
                                    }"
                                    v-for="message in conversation.messages"
                                    :key="message.id"
                                >
                                    <p class="prose" v-html="message.body"></p>
                                    <span class="text-xs text-gray-800">{{
                                        message.role === "assistant"
                                            ? "Assistant"
                                            : "Moi"
                                    }}</span>
                                </li>
                                <li
                                    v-show="isAnswering"
                                    class="w-[80%] p-4 rounded-lg col-span-8 bg-blue-200"
                                >
                                    <LoadingIcon
                                        class="w-5 h-5 animate-spin text-blue-900"
                                    />
                                    <span class="text-xs text-gray-800"
                                        >Assistant</span
                                    >
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-span-12">
                        <InputLabel for="prompt" value="Votre message" />
                        <TextareaInput
                            id="prompt"
                            v-model="form.prompt"
                            type="text"
                            class="mt-1 block w-full"
                            @keydown.enter.ctrl="send"
                        />
                        <div class="flex justify-between items-center mt-2">
                            <div class="flex flex-col">
                                <InputError :message="form.errors.prompt" />
                                <InputError
                                    :message="formAnswer.errors.model"
                                />
                                <InputError
                                    v-if="isTokenLimitReached"
                                    message="La limite de token est atteinte."
                                />
                            </div>
                            <div
                                class="text-xs text-gray-800"
                                :class="{
                                    'text-red-600': isTokenLimitReached,
                                }"
                            >
                                {{ conversation.token_count }} tokens
                            </div>
                        </div>
                    </div>
                </template>

                <template #actions>
                    <div class="flex space-x-4 items-center grow">
                        <InputLabel for="model" value="Modèle" />
                        <select
                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            id="model"
                            @change="updateSelectedModel($event.target.value)"
                        >
                            <option
                                v-for="model in models"
                                :key="model.value"
                                :value="model.value"
                                :selected="model.value === props.selectedModel"
                            >
                                {{ model.name }}
                                {{ Math.round(model.maxTokens / 1000) }}k
                            </option>
                        </select>
                    </div>
                    <ActionMessage :on="form.recentlySuccessful" class="me-3">
                        Message envoyé.
                    </ActionMessage>

                    <PrimaryButton
                        :class="{
                            'opacity-25':
                                form.processing ||
                                isAnswering ||
                                isTokenLimitReached,
                        }"
                        :disabled="
                            form.processing ||
                            isAnswering ||
                            isTokenLimitReached
                        "
                    >
                        Envoyer
                    </PrimaryButton>
                </template>
            </FormSection>
        </div>
    </AppLayout>
</template>
