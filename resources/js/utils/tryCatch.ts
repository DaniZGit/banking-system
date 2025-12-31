export const tryCatch = async <T = any, E = Error>(promise: Promise<T>): Promise<[result: undefined | T, error: undefined | E]> => {
    try {
        return [await promise, undefined];
    } catch (error) {
        return [undefined, error as E];
    }
};
