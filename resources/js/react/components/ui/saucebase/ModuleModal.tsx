import { useModules } from '@/hooks/useModules';
import { useT } from '@/i18n';
import { BookOpen, Check, Copy, Terminal, X } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';
import { createPortal } from 'react-dom';
import { toast } from 'sonner';
import type { Module } from './index';

interface ModuleModalProps {
    selectedMod: Module | null;
    onClose: () => void;
}

function installCommand(mod: Module, installed: boolean): string {
    if (mod.id === 'custom')
        return 'php artisan saucebase:recipe MyAmazingModuleIdea';
    const composer = installed
        ? `composer update saucebase/${mod.id}`
        : `composer require saucebase/${mod.id}`;
    const base = `${composer}\nphp artisan migrate\nphp artisan modules:seed --module=${mod.id}`;
    return mod.customCommands?.length
        ? `${base}\n${mod.customCommands.join('\n')}`
        : base;
}

export function ModuleModal({ selectedMod, onClose }: ModuleModalProps) {
    const t = useT();
    const { has: isInstalled } = useModules();
    const [copied, setCopied] = useState(false);
    const copyTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null);

    // `mod` keeps the rendered module alive during the exit animation.
    // `isOpen` drives the CSS transitions (false = closed state / starting state).
    const [mod, setMod] = useState<Module | null>(null);
    const [isOpen, setIsOpen] = useState(false);
    const closeTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null);
    const openRafRef = useRef<number | null>(null);

    useEffect(() => {
        if (selectedMod) {
            if (closeTimerRef.current) {
                clearTimeout(closeTimerRef.current);
                closeTimerRef.current = null;
            }
            setMod(selectedMod);
            // Double-raf ensures the element is in the DOM before the transition starts
            openRafRef.current = requestAnimationFrame(() => {
                openRafRef.current = requestAnimationFrame(() =>
                    setIsOpen(true),
                );
            });
        } else {
            if (openRafRef.current) {
                cancelAnimationFrame(openRafRef.current);
                openRafRef.current = null;
            }
            setIsOpen(false);
            // Wait for leave animation to finish before unmounting
            closeTimerRef.current = setTimeout(() => setMod(null), 300);
        }

        return () => {
            if (closeTimerRef.current) clearTimeout(closeTimerRef.current);
            if (openRafRef.current) cancelAnimationFrame(openRafRef.current);
        };
    }, [selectedMod]);

    useEffect(() => {
        return () => {
            if (copyTimerRef.current) clearTimeout(copyTimerRef.current);
        };
    }, []);

    const copyCommand = useCallback(() => {
        if (!mod) return;
        navigator.clipboard
            .writeText(installCommand(mod, isInstalled(mod.id)))
            .then(() => {
                toast.success(t('Copied to clipboard'));
                if (copyTimerRef.current) clearTimeout(copyTimerRef.current);
                setCopied(true);
                copyTimerRef.current = setTimeout(() => setCopied(false), 2000);
            })
            .catch(() => toast.error(t('Failed to copy')));
    }, [mod, t]);

    if (!mod) return null;

    const Icon = mod.icon;

    return createPortal(
        <>
            {/* Backdrop — enter 200ms, leave 150ms */}
            <div
                className="bg-background/50 fixed inset-0 z-50 backdrop-blur-md"
                style={{
                    opacity: isOpen ? 1 : 0,
                    transition: isOpen
                        ? 'opacity 200ms ease'
                        : 'opacity 150ms ease',
                    pointerEvents: isOpen ? undefined : 'none',
                }}
                onClick={onClose}
            />

            {/* Modal card — enter 300ms ease-out, leave 200ms ease-in */}
            <div
                className="pointer-events-none fixed inset-0 z-50 flex items-center justify-center p-6 shadow-lg"
                style={{
                    opacity: isOpen ? 1 : 0,
                    transform: isOpen
                        ? 'translateY(0) scale(1)'
                        : 'translateY(1.5rem) scale(0.96)',
                    transition: isOpen
                        ? 'opacity 300ms ease-out, transform 300ms ease-out'
                        : 'opacity 200ms ease-in, transform 200ms ease-in',
                }}
            >
                <div
                    className="pointer-events-auto relative w-full max-w-xl"
                    onClick={(e) => e.stopPropagation()}
                >
                    <div
                        className="bg-card/90 border-border relative z-10 flex flex-col gap-3 rounded-xl border p-6 shadow-[0px_5px_0_0_color-mix(in_oklch,var(--color-white)_85%,black)] dark:shadow-[0px_5px_0_0_color-mix(in_oklch,var(--color-white)_20%,black)]"
                        style={
                            {
                                '--mod-color': `var(${mod.color})`,
                            } as React.CSSProperties
                        }
                    >
                        {/* Close button */}
                        <button
                            className="text-muted-foreground hover:text-foreground hover:border-foreground absolute top-3 right-3 z-20 cursor-pointer rounded-full border p-1.5 transition-colors"
                            onClick={onClose}
                        >
                            <X className="size-4" />
                        </button>

                        {/* Header */}
                        <div className="flex items-center gap-3">
                            <div
                                className="flex size-11 shrink-0 items-center justify-center rounded-full"
                                style={{ background: `var(${mod.color})` }}
                            >
                                <Icon
                                    className="size-5 text-white"
                                    aria-hidden="true"
                                />
                            </div>
                            <div className="flex flex-1 flex-wrap items-center gap-2">
                                <h2 className="text-foreground text-xl leading-tight font-bold">
                                    {t(mod.title)}
                                </h2>
                                {isInstalled(mod.id) && (
                                    <span className="border-primary text-primary ml-2 rounded-full border px-3 py-1 text-sm font-semibold">
                                        {t('Installed')}
                                    </span>
                                )}
                            </div>
                        </div>

                        {/* Description */}
                        <p className="text-muted-foreground py-2 leading-relaxed">
                            {t(mod.description)}
                        </p>

                        {/* Features */}
                        <ul className="grid grid-cols-2 gap-x-4 gap-y-1.5 rounded-sm border p-4">
                            {mod.features.map((feature) => (
                                <li
                                    key={feature}
                                    className="text-foreground flex items-center gap-2 text-sm"
                                >
                                    <Check
                                        className="size-3.5 shrink-0"
                                        style={{ color: `var(${mod.color})` }}
                                        aria-hidden="true"
                                    />
                                    {t(feature)}
                                </li>
                            ))}
                        </ul>

                        {/* Install command */}
                        {mod.href !== null && (
                            <div className="mt-2 flex flex-col gap-2">
                                <div className="flex items-center gap-3 rounded-xl bg-gray-950 px-4 py-3 shadow-sm dark:bg-gray-900">
                                    <Terminal
                                        className="mt-0.5 size-4 shrink-0 self-start text-gray-500"
                                        aria-hidden="true"
                                    />
                                    <code className="flex-1 whitespace-pre text-sm text-green-400">
                                        {installCommand(mod, isInstalled(mod.id))}
                                    </code>
                                    <button
                                        className="cursor-pointer self-start text-gray-300 transition-colors hover:text-gray-300"
                                        onClick={copyCommand}
                                    >
                                        {copied ? (
                                            <Check className="size-4 text-green-400" />
                                        ) : (
                                            <Copy className="size-4" />
                                        )}
                                    </button>
                                </div>
                                {mod.id !== 'custom' && (
                                    <p className="text-muted-foreground pb-2 text-center text-sm">
                                        {t(
                                            'This module may require additional steps after installation, check the docs',
                                        )}
                                    </p>
                                )}
                            </div>
                        )}

                        {/* CTA */}
                        <div className="-mx-6 border-t px-6 pt-4">
                            {mod.href !== null ? (
                                <a
                                    href={mod.href}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="flex w-full items-center justify-center gap-2 rounded-full px-6 py-2.5 text-sm font-semibold text-white shadow-[0_5px_0_0_color-mix(in_oklch,var(--mod-color)_85%,black)] transition-all duration-200 hover:-translate-y-0.5 hover:shadow-[0_7px_0_0_color-mix(in_oklch,var(--mod-color)_85%,black)]"
                                    style={{ background: `var(${mod.color})` }}
                                >
                                    <BookOpen
                                        className="size-4"
                                        aria-hidden="true"
                                    />
                                    {t('Read the Documentation')}
                                </a>
                            ) : (
                                <span className="bg-muted/90 border-muted-foreground/20 text-muted-foreground flex w-full items-center justify-center gap-2 rounded-full border px-6 py-2.5 text-sm font-semibold">
                                    {t('Coming Soon')}
                                </span>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </>,
        document.body,
    );
}
