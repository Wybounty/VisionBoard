import { Form, Head, usePage } from '@inertiajs/react';
import { Sparkles, WandSparkles } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import InputError from '@/components/input-error';
import type { Auth } from '@/types';

type VisionBoard = {
    id: number;
    slug: string;
    title: string;
    year: number;
};

type Theme = {
    title: string;
    description: string;
    motivational_phrase: string;
};

type VisionBoardBrief = {
    id: number;
    summary: string;
    data: {
        themes: Theme[];
    };
    created_at: string | null;
};

type PageProps = {
    auth: Auth;
    visionBoard: VisionBoard;
    latestBrief: VisionBoardBrief | null;
    flash?: {
        success?: string;
    };
};

export default function VisionBoardBriefPage() {
    const { visionBoard, latestBrief, flash } = usePage<PageProps>().props;

    return (
        <>
            <Head title={`${visionBoard.title} Brief`} />

            <div className="relative overflow-hidden rounded-3xl border border-border/60 bg-[radial-gradient(circle_at_top_left,_rgba(124,58,237,0.16),_transparent_42%),radial-gradient(circle_at_top_right,_rgba(236,72,153,0.16),_transparent_38%),linear-gradient(180deg,_rgba(255,255,255,0.96),_rgba(250,250,250,0.9))] p-6 shadow-sm dark:bg-[radial-gradient(circle_at_top_left,_rgba(124,58,237,0.24),_transparent_42%),radial-gradient(circle_at_top_right,_rgba(236,72,153,0.18),_transparent_38%),linear-gradient(180deg,_rgba(15,15,15,0.96),_rgba(12,12,12,0.9))] md:p-8">
                <div className="absolute inset-0 -z-10 bg-[linear-gradient(135deg,rgba(255,255,255,0.22),transparent_35%,rgba(255,255,255,0.08))]" />

                <div className="grid gap-8 lg:grid-cols-[1.1fr_0.9fr]">
                    <section className="space-y-6">
                        <div className="space-y-2">
                            <p className="text-xs font-semibold uppercase tracking-[0.3em] text-muted-foreground">
                                Vision Board {visionBoard.year}
                            </p>
                            <h1 className="max-w-2xl text-4xl font-semibold tracking-tight text-foreground md:text-5xl">
                                {visionBoard.title}
                            </h1>
                            <p className="max-w-2xl text-base leading-7 text-muted-foreground">
                                Décrivez vos idées, vos envies et la vie que vous souhaitez construire.
                                Nous en extrairons ensuite les thèmes clés pour générer le Vision Board.
                            </p>
                        </div>

                        {flash?.success && (
                            <div className="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300">
                                {flash.success}
                            </div>
                        )}

                        <Card className="border-border/70 bg-background/80 backdrop-blur">
                            <CardContent className="p-6">
                                <Form
                                    method="post"
                                    action={`/vision-boards/${visionBoard.slug}/brief`}
                                    className="space-y-4"
                                    options={{ preserveScroll: true }}
                                >
                                    {({ processing, errors }) => (
                                        <>
                                            <div className="space-y-2">
                                                <Label htmlFor="brief" className="text-sm font-medium">
                                                    Mon brief
                                                </Label>
                                                <Textarea
                                                    id="brief"
                                                    name="brief"
                                                    rows={10}
                                                    placeholder="Exemple : je veux bâtir une vie plus libre, lancer un projet créatif, voyager davantage et retrouver un meilleur équilibre..."
                                                    className="min-h-56 resize-none bg-background/90"
                                                    required
                                                />
                                                <InputError message={errors.brief} />
                                            </div>

                                            <div className="flex flex-wrap items-center justify-between gap-3">
                                                <p className="text-sm text-muted-foreground">
                                                    Plus votre brief est concret, plus l&apos;analyse sera utile.
                                                </p>
                                                <Button type="submit" disabled={processing} className="min-w-52">
                                                    {processing ? (
                                                        <>
                                                            <Sparkles className="h-4 w-4 animate-pulse" />
                                                            Analyse en cours...
                                                        </>
                                                    ) : (
                                                        <>
                                                            <WandSparkles className="h-4 w-4" />
                                                            Analyser mon brief
                                                        </>
                                                    )}
                                                </Button>
                                            </div>
                                        </>
                                    )}
                                </Form>
                            </CardContent>
                        </Card>
                    </section>

                    <aside className="space-y-4">
                        <Card className="border-border/70 bg-background/80 backdrop-blur">
                            <CardHeader>
                                <CardTitle className="text-base">Dernière analyse</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-5">
                                {latestBrief ? (
                                    <>
                                        <div className="space-y-2">
                                            <p className="text-sm font-medium text-foreground">
                                                Résumé
                                            </p>
                                            <p className="text-sm leading-6 text-muted-foreground">
                                                {latestBrief.summary}
                                            </p>
                                        </div>

                                        <div className="grid gap-3">
                                            {latestBrief.data.themes.map((theme) => (
                                                <div
                                                    key={`${theme.title}-${theme.description}`}
                                                    className="rounded-2xl border border-border/70 bg-muted/30 p-4"
                                                >
                                                    <p className="text-sm font-semibold text-foreground">
                                                        {theme.title}
                                                    </p>
                                                    <p className="mt-1 text-sm leading-6 text-muted-foreground">
                                                        {theme.description}
                                                    </p>
                                                    <p className="mt-3 text-sm font-medium text-foreground">
                                                        {theme.motivational_phrase}
                                                    </p>
                                                </div>
                                            ))}
                                        </div>
                                    </>
                                ) : (
                                    <div className="space-y-3 text-sm text-muted-foreground">
                                        <p>
                                            Aucune analyse n&apos;a encore été générée pour ce Vision Board.
                                        </p>
                                        <p>
                                            Remplissez le brief pour obtenir un résumé et les thèmes clés de votre vision.
                                        </p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </aside>
                </div>
            </div>
        </>
    );
}

VisionBoardBriefPage.layout = {
    breadcrumbs: [
        {
            title: 'Vision Boards',
            href: '/vision-boards',
        },
    ],
};
