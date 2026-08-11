import AlertMessage from '@/components/AlertMessage';
import AppLogo from '@/components/AppLogo';
import Footer from '@/components/Footer';
import PageTransition from '@/components/PageTransition';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Head, Link, usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';

interface CardLayoutProps {
    title?: string;
    description?: string;
    cardClass?: string;
    children: ReactNode;
    outside?: ReactNode;

    /**
     * Optional mark, centred above the heading. A page with a single message to deliver
     * gets one; the sign-in and registration forms pass nothing and are unchanged.
     */
    icon?: ReactNode;

    /**
     * `title` stays a string for `<Head>`; this is for headings that need markup of
     * their own rather than plain text.
     */
    heading?: ReactNode;
}

/**
 * A centred card on an otherwise empty page.
 *
 * Signing in, registering and naming a workspace are one flow to the person doing them,
 * so they share a layout rather than each module inventing its own. It lives in core for
 * the same reason `Card` and `Button` do: it is a presentational primitive, and a module
 * importing another module's layout is the coupling this avoids.
 */
export default function CardLayout({
    title,
    description,
    cardClass,
    children,
    outside,
    icon,
    heading,
}: CardLayoutProps) {
    const page = usePage();
    const status = page.props.status as string | undefined;
    const error = page.props.error as string | undefined;

    return (
        <div className="flex min-h-dvh flex-col items-center gap-6">
            <div className="mt-6">
                <Head title={title} />
                <Link href={route('index')} className="mt-6 font-medium">
                    <AppLogo size="md" showText={true} />
                </Link>
            </div>

            <div className="flex w-full grow flex-col items-center">
                <div className="w-full px-4 min-[450px]:w-auto min-[450px]:min-w-md min-[450px]:px-0">
                    <Card className={cardClass}>
                        <CardHeader className="px-8 text-center">
                            {icon && (
                                <div
                                    className="mb-2 flex justify-center"
                                    data-testid="card-icon"
                                >
                                    {icon}
                                </div>
                            )}
                            <CardTitle className="text-2xl">
                                {heading ?? title}
                            </CardTitle>
                            <CardDescription>{description}</CardDescription>
                        </CardHeader>
                        <CardContent className="px-8">
                            <PageTransition>
                                {status || error ? (
                                    <div data-testid="alert">
                                        <AlertMessage
                                            message={status || error}
                                            variant={
                                                status ? 'success' : 'error'
                                            }
                                        />
                                    </div>
                                ) : null}
                                {children}
                            </PageTransition>
                        </CardContent>
                    </Card>
                </div>
                {outside}
            </div>
            <Footer />
        </div>
    );
}
