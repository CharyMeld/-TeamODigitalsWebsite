<script setup>
import { ref, computed } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    user: {
        type: Object,
        required: true,
        default: () => ({})
    }
});

const page = usePage();
const photoInput = ref(null);
const photoPreview = ref(null);

// Safely get user data with fallbacks
const userData = computed(() => ({
    name: props.user?.name || '',
    email: props.user?.email || '',
    username: props.user?.username || '',
    employee_id: props.user?.employee_id || '',
    phone: props.user?.phone || '',
    address: props.user?.address || '',
    gender: props.user?.gender || '',
    date_of_birth: props.user?.date_of_birth || '',
    marital_status: props.user?.marital_status || '',
    emergency_contact: props.user?.emergency_contact || '',
    local_government: props.user?.local_government || '',
    state: props.user?.state || '',
    country: props.user?.country || '',
    department: props.user?.department || '',
    salary: props.user?.salary || '',
    hire_date: props.user?.hire_date || '',
    status: props.user?.status || 'active',
    role: props.user?.roles?.[0]?.name || 'employee'
}));

const form = useForm({
    _method: 'PUT',
    name: userData.value.name,
    email: userData.value.email,
    username: userData.value.username,
    employee_id: userData.value.employee_id,
    phone: userData.value.phone,
    address: userData.value.address,
    gender: userData.value.gender,
    date_of_birth: userData.value.date_of_birth,
    marital_status: userData.value.marital_status,
    emergency_contact: userData.value.emergency_contact,
    local_government: userData.value.local_government,
    state: userData.value.state,
    country: userData.value.country,
    department: userData.value.department,
    salary: userData.value.salary,
    hire_date: userData.value.hire_date,
    status: userData.value.status,
    role: userData.value.role,
    photo: null,
});

const verificationLinkSent = ref(null);
const photoPreviewUrl = computed(() => {
    return photoPreview.value || props.user?.profile_photo_url || null;
});

const updateProfileInformation = () => {
    if (photoInput.value) {
        form.photo = photoInput.value.files[0];
    }

    form.post(route('user-profile-information.update'), {
        errorBag: 'updateProfileInformation',
        preserveScroll: true,
        onSuccess: () => clearPhotoFileInput(),
    });
};

const sendEmailVerification = () => {
    verificationLinkSent.value = true;
};

const selectNewPhoto = () => {
    photoInput.value.click();
};

const updatePhotoPreview = () => {
    const photo = photoInput.value.files[0];

    if (!photo) return;

    const reader = new FileReader();

    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };

    reader.readAsDataURL(photo);
};

const deletePhoto = () => {
    router.delete(route('current-user-photo.destroy'), {
        preserveScroll: true,
        onSuccess: () => {
            photoPreview.value = null;
            clearPhotoFileInput();
        },
    });
};

const clearPhotoFileInput = () => {
    if (photoInput.value?.value) {
        photoInput.value.value = null;
    }
};
</script>

<template>
    <FormSection @submitted="updateProfileInformation">
        <template #title>
            Profile Information
        </template>

        <template #description>
            Update your account's profile information and email address.
        </template>

        <template #form>
            <!-- Profile Photo -->
            <div v-if="$page.props.jetstream?.managesProfilePhotos" class="col-span-6 sm:col-span-4">
                <input
                    ref="photoInput"
                    type="file"
                    class="hidden"
                    accept="image/*"
                    @change="updatePhotoPreview"
                >

                <InputLabel for="photo" value="Photo" />

                <!-- Current Profile Photo -->
                <div v-show="!photoPreview" class="mt-2">
                    <img :src="props.user?.profile_photo_url || '/default-avatar.png'" :alt="props.user?.name || 'User'" class="rounded-full h-20 w-20 object-cover">
                </div>

                <!-- New Profile Photo Preview -->
                <div v-show="photoPreview" class="mt-2">
                    <span
                        class="block rounded-full w-20 h-20 bg-cover bg-no-repeat bg-center"
                        :style="'background-image: url(\'' + photoPreview + '\');'"
                    />
                </div>

                <SecondaryButton class="mt-2 mr-2" type="button" @click.prevent="selectNewPhoto">
                    Select A New Photo
                </SecondaryButton>

                <SecondaryButton
                    v-if="props.user?.profile_photo_path"
                    type="button"
                    class="mt-2"
                    @click.prevent="deletePhoto"
                >
                    Remove Photo
                </SecondaryButton>

                <InputError :message="form.errors.photo" class="mt-2" />
            </div>

            <!-- Name -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="name" value="Name" />
                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    autocomplete="name"
                />
                <InputError :message="form.errors.name" class="mt-2" />
            </div>

            <!-- Email -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    required
                    autocomplete="username"
                />
                <InputError :message="form.errors.email" class="mt-2" />

                <div v-if="$page.props.jetstream?.hasEmailVerification && props.user?.email_verified_at === null">
                    <p class="text-sm mt-2">
                        Your email address is unverified.

                        <Link
                            :href="route('verification.send')"
                            method="post"
                            as="button"
                            class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            @click.prevent="sendEmailVerification"
                        >
                            Click here to re-send the verification email.
                        </Link>
                    </p>

                    <div v-show="verificationLinkSent" class="mt-2 font-medium text-sm text-green-600">
                        A new verification link has been sent to your email address.
                    </div>
                </div>
            </div>

            <!-- Username -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="username" value="Username" />
                <TextInput
                    id="username"
                    v-model="form.username"
                    type="text"
                    class="mt-1 block w-full"
                    autocomplete="username"
                />
                <InputError :message="form.errors.username" class="mt-2" />
            </div>

            <!-- Employee ID -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="employee_id" value="Employee ID" />
                <TextInput
                    id="employee_id"
                    v-model="form.employee_id"
                    type="text"
                    class="mt-1 block w-full"
                />
                <InputError :message="form.errors.employee_id" class="mt-2" />
            </div>

            <!-- Phone -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="phone" value="Phone" />
                <TextInput
                    id="phone"
                    v-model="form.phone"
                    type="tel"
                    class="mt-1 block w-full"
                />
                <InputError :message="form.errors.phone" class="mt-2" />
            </div>

            <!-- Address -->
            <div class="col-span-6">
                <InputLabel for="address" value="Address" />
                <textarea
                    id="address"
                    v-model="form.address"
                    rows="3"
                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                />
                <InputError :message="form.errors.address" class="mt-2" />
            </div>

            <!-- Gender, Date of Birth, Marital Status -->
            <div class="col-span-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <InputLabel for="gender" value="Gender" />
                    <select
                        id="gender"
                        v-model="form.gender"
                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                    >
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                    <InputError :message="form.errors.gender" class="mt-2" />
                </div>

                <div>
                    <InputLabel for="date_of_birth" value="Date of Birth" />
                    <TextInput
                        id="date_of_birth"
                        v-model="form.date_of_birth"
                        type="date"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.date_of_birth" class="mt-2" />
                </div>

                <div>
                    <InputLabel for="marital_status" value="Marital Status" />
                    <select
                        id="marital_status"
                        v-model="form.marital_status"
                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full"
                    >
                        <option value="">Select Status</option>
                        <option value="single">Single</option>
                        <option value="married">Married</option>
                        <option value="divorced">Divorced</option>
                        <option value="widowed">Widowed</option>
                    </select>
                    <InputError :message="form.errors.marital_status" class="mt-2" />
                </div>
            </div>

            <!-- Emergency Contact and Local Government -->
            <div class="col-span-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <InputLabel for="emergency_contact" value="Emergency Contact" />
                    <TextInput
                        id="emergency_contact"
                        v-model="form.emergency_contact"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.emergency_contact" class="mt-2" />
                </div>

                <div>
                    <InputLabel for="local_government" value="Local Government" />
                    <TextInput
                        id="local_government"
                        v-model="form.local_government"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.local_government" class="mt-2" />
                </div>
            </div>

            <!-- State, Country, Department -->
            <div class="col-span-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <InputLabel for="state" value="State" />
                    <TextInput
                        id="state"
                        v-model="form.state"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.state" class="mt-2" />
                </div>

                <div>
                    <InputLabel for="country" value="Country" />
                    <TextInput
                        id="country"
                        v-model="form.country"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.country" class="mt-2" />
                </div>

                <div>
                    <InputLabel for="department" value="Department" />
                    <TextInput
                        id="department"
                        v-model="form.department"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.department" class="mt-2" />
                </div>
            </div>

            <!-- Salary and Hire Date -->
            <div class="col-span-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <InputLabel for="salary" value="Salary" />
                    <TextInput
                        id="salary"
                        v-model="form.salary"
                        type="number"
                        step="0.01"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.salary" class="mt-2" />
                </div>

                <div>
                    <InputLabel for="hire_date" value="Hire Date" />
                    <TextInput
                        id="hire_date"
                        v-model="form.hire_date"
                        type="date"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.hire_date" class="mt-2" />
                </div>
            </div>
        </template>

        <template #actions>
            <ActionMessage :on="form.recentlySuccessful" class="mr-3">
                Saved.
            </ActionMessage>

            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Save
            </PrimaryButton>
        </template>
    </FormSection>
</template>
