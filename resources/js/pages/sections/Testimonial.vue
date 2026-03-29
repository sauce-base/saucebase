<template>
    <section
        class="bg-background relative isolate overflow-hidden px-6 py-24 sm:py-32 lg:px-8"
    >
        <div
            class="absolute inset-0 -z-10 bg-[radial-gradient(45rem_50rem_at_top,var(--color-primary),transparent)] opacity-10"
        ></div>
        <div
            class="bg-background shadow-primary/10 ring-primary/5 absolute inset-y-0 right-1/2 -z-10 mr-16 w-[200%] origin-bottom-left skew-x-[-30deg] shadow-xl ring-1 sm:mr-28 lg:mr-0 xl:mr-16 xl:origin-center"
        ></div>
        <div class="mx-auto max-w-2xl lg:max-w-4xl">
            <img
                src="/images/logo.svg"
                alt=""
                class="mx-auto h-12 dark:hidden"
            />
            <img
                src="/images/logo-white.svg"
                alt=""
                class="mx-auto h-12 not-dark:hidden"
            />
            <figure class="mt-10">
                <blockquote
                    class="text-foreground text-center text-xl/8 font-semibold sm:text-2xl/9"
                >
                    <p>
                        “We shipped our SaaS in two weeks instead of two months.
                        Auth, billing, and the admin panel were already wired
                        up, we just built the actual product.”
                    </p>
                </blockquote>
                <figcaption class="mt-10">
                    <img
                        src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80"
                        alt=""
                        class="mx-auto size-10 rounded-xl"
                    />
                    <div
                        class="mt-4 flex items-center justify-center space-x-3 text-base"
                    >
                        <div class="text-foreground font-semibold">
                            Liz Peres
                        </div>
                        <svg
                            viewBox="0 0 2 2"
                            width="3"
                            height="3"
                            aria-hidden="true"
                            class="fill-foreground"
                        >
                            <circle r="1" cx="1" cy="1" />
                        </svg>
                        <div class="text-muted-foreground">
                            Founder, Sauceable
                        </div>
                    </div>
                </figcaption>
            </figure>
        </div>
    </section>
</template>
