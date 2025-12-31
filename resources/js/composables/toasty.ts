import { toast } from 'vue-sonner';

export const useToasty = () => {
    return {
        error: (message: string) =>
            toast.error(message, {
                description: `Date: ${new Date().toLocaleString()}`,
                duration: 5000,
                position: 'bottom-right',
            }),
    };
};
