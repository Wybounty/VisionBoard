import { Form, Head, Link, router, usePage } from '@inertiajs/react';
import { ArrowRight, Pencil, Plus, Trash2 } from 'lucide-react';
import { useMemo, useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { Auth } from '@/types';

type VisionBoard = {
    id: number;
    slug: string;
    title: string;
    year: number;
};

type PageProps = {
    auth: Auth;
    visionBoards: VisionBoard[];
    flash?: {
        success?: string;
    };
};

export default function VisionBoardsIndex() {
    const { visionBoards, flash } = usePage<PageProps>().props;
    const [activeBoard, setActiveBoard] = useState<VisionBoard | null>(null);
    const [isDialogOpen, setIsDialogOpen] = useState(false);

    const initialValues = useMemo(
        () => ({
            title: activeBoard?.title ?? '',
            year: activeBoard?.year ?? new Date().getFullYear(),
        }),
        [activeBoard],
    );

    const handleOpenCreate = () => {
        setActiveBoard(null);
        setIsDialogOpen(true);
    };

    const handleOpenEdit = (board: VisionBoard) => {
        setActiveBoard(board);
        setIsDialogOpen(true);
    };

    const handleClose = () => {
        setIsDialogOpen(false);
        setActiveBoard(null);
    };

    return (
        <>
            <Head title="Vision Boards" />

            <div className="space-y-6">
                <div className="flex items-center justify-between gap-4">
                    <div>
                        <h2 className="text-xl font-semibold tracking-tight">
                            Vision Boards
                        </h2>
                        <p className="text-sm text-muted-foreground">
                            Organize your goals and inspirations by year.
                        </p>
                    </div>

                    <Button onClick={handleOpenCreate}>
                        <Plus className="mr-2 h-4 w-4" />
                        New board
                    </Button>
                </div>

                {flash?.success && (
                    <div className="rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-950/40 dark:text-green-300">
                        {flash.success}
                    </div>
                )}

                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    {visionBoards.map((board) => (
                        <Card key={board.id} className="shadow-sm">
                            <Link
                                href={`/vision-boards/${board.slug}/brief`}
                                className="block rounded-t-xl transition hover:bg-muted/40"
                            >
                                <CardHeader>
                                    <CardTitle>{board.title}</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-sm text-muted-foreground">{board.year}</p>
                                </CardContent>
                            </Link>
                            <CardFooter className="flex flex-wrap items-center justify-between gap-2">
                                <Button asChild variant="outline" size="sm">
                                    <Link href={`/vision-boards/${board.slug}/brief`}>
                                        Ouvrir le brief
                                        <ArrowRight className="h-4 w-4" />
                                    </Link>
                                </Button>

                                <div className="flex items-center gap-2">
                                <Button
                                    variant="secondary"
                                    size="sm"
                                    onClick={() => handleOpenEdit(board)}
                                >
                                    <Pencil className="mr-2 h-4 w-4" />
                                    Edit
                                </Button>

                                <Dialog>
                                    <DialogTrigger asChild>
                                        <Button variant="destructive" size="sm">
                                            <Trash2 className="mr-2 h-4 w-4" />
                                            Delete
                                        </Button>
                                    </DialogTrigger>
                                    <DialogContent>
                                        <DialogHeader>
                                            <DialogTitle>
                                                Delete this vision board?
                                            </DialogTitle>
                                            <DialogDescription>
                                                This action will remove the board from your list.
                                            </DialogDescription>
                                        </DialogHeader>
                                        <DialogFooter className="gap-2">
                                            <DialogClose asChild>
                                                <Button variant="secondary">
                                                    Cancel
                                                </Button>
                                            </DialogClose>
                                            <Button
                                                variant="destructive"
                                                onClick={() =>
                                                    router.delete(`/vision-boards/${board.slug}`)
                                                }
                                            >
                                                Delete
                                            </Button>
                                        </DialogFooter>
                                    </DialogContent>
                                </Dialog>
                                </div>
                            </CardFooter>
                        </Card>
                    ))}
                </div>
            </div>

            <Dialog open={isDialogOpen} onOpenChange={(open) => (!open ? handleClose() : setIsDialogOpen(true))}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>
                            {activeBoard ? 'Edit vision board' : 'Create vision board'}
                        </DialogTitle>
                        <DialogDescription>
                            {activeBoard
                                ? 'Update the details of this vision board.'
                                : 'Add a new vision board to your dashboard.'}
                        </DialogDescription>
                    </DialogHeader>

                    <Form
                        method={activeBoard ? 'put' : 'post'}
                        action={activeBoard ? `/vision-boards/${activeBoard.slug}` : '/vision-boards'}
                        onSuccess={handleClose}
                        className="space-y-4"
                    >
                        <div className="space-y-2">
                            <Label htmlFor="title">Title</Label>
                            <Input
                                id="title"
                                name="title"
                                defaultValue={initialValues.title}
                                placeholder="Vision board title"
                                required
                            />
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="year">Year</Label>
                            <Input
                                id="year"
                                name="year"
                                type="number"
                                defaultValue={initialValues.year}
                                min="1900"
                                max="2100"
                                required
                            />
                        </div>

                        <DialogFooter className="gap-2">
                            <DialogClose asChild>
                                <Button variant="secondary">Cancel</Button>
                            </DialogClose>
                            <Button type="submit">
                                {activeBoard ? 'Save changes' : 'Create board'}
                            </Button>
                        </DialogFooter>
                    </Form>
                </DialogContent>
            </Dialog>
        </>
    );
}

VisionBoardsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Vision Boards',
            href: '/vision-boards',
        },
    ],
};
