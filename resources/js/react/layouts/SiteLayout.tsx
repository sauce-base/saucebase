import Footer from '@/components/Footer';
import Header from '@/components/Header';
import { useSettings } from '@/hooks/useSettings';
import { Head } from '@inertiajs/react';
import type { ReactNode } from 'react';

interface SiteLayoutProps {
    title?: string;
    description?: string;
    image?: string;
    canonical?: string;
    type?: 'website' | 'article';
    children?: ReactNode;
}

export default function SiteLayout({
    title,
    description,
    image,
    canonical,
    type = 'website',
    children,
}: SiteLayoutProps) {
    const settings = useSettings();
    const pageDescription = description ?? settings.general.site_description;

    return (
        <>
            <Head title={title}>
                {pageDescription && (
                    <meta
                        head-key="description"
                        data-testid="app-description"
                        name="description"
                        content={pageDescription}
                    />
                )}
                {canonical && <link rel="canonical" href={canonical} />}
                <meta property="og:type" content={type} />
                {title && <meta property="og:title" content={title} />}
                {pageDescription && (
                    <meta property="og:description" content={pageDescription} />
                )}
                {image && <meta property="og:image" content={image} />}
                {canonical && <meta property="og:url" content={canonical} />}
                <meta
                    property="og:site_name"
                    content={settings.general.site_name}
                />
                <meta
                    name="twitter:card"
                    content={image ? 'summary_large_image' : 'summary'}
                />
                {title && <meta name="twitter:title" content={title} />}
                {pageDescription && (
                    <meta
                        name="twitter:description"
                        content={pageDescription}
                    />
                )}
                {image && <meta name="twitter:image" content={image} />}
            </Head>
            <div className="bg-background relative isolate flex min-h-screen flex-col">
                <Header />
                {children}
                <Footer />
            </div>
        </>
    );
}
