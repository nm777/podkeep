import SheetPanel from '@/components/sheet-panel';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import UserRow from '@/components/user-row';
import AdminLayout from '@/layouts/admin-layout';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { useState } from 'react';

interface User {
    id: number;
    name: string;
    email: string;
    is_admin: boolean;
    approval_status: 'pending' | 'approved' | 'rejected';
    approved_at?: string;
    rejected_at?: string;
    rejection_reason?: string;
    created_at: string;
}

interface PageProps {
    [key: string]: unknown;
    users: User[];
    flash?: {
        success?: string;
        error?: string;
    };
}

export default function UserManagement() {
    const { users, flash } = usePage<PageProps>().props;
    const [rejectingUser, setRejectingUser] = useState<User | null>(null);
    const [showRejected, setShowRejected] = useState(false);

    const approveForm = useForm({});
    const rejectForm = useForm({ reason: '' });
    const toggleAdminForm = useForm({});

    const handleApprove = (user: User) => {
        approveForm.post(route('admin.users.approve', user.id));
    };

    const handleReject = () => {
        if (!rejectingUser) return;

        rejectForm.post(route('admin.users.reject', rejectingUser.id), {
            onSuccess: () => {
                setRejectingUser(null);
                rejectForm.setData('reason', '');
            },
        });
    };

    const handleToggleAdmin = (user: User) => {
        toggleAdminForm.post(route('admin.users.toggle-admin', user.id));
    };

    const closeRejectPanel = () => {
        setRejectingUser(null);
        rejectForm.setData('reason', '');
        rejectForm.clearErrors();
    };

    const filteredUsers = users.filter((user) => showRejected || user.approval_status !== 'rejected');

    return (
        <AdminLayout>
            <Head title="User Management" />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="mb-6">
                    <div className="flex items-center justify-between">
                        <div>
                            <Link
                                href={route('dashboard')}
                                className="mb-2 inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                            >
                                <ArrowLeft className="h-4 w-4" />
                                Back
                            </Link>
                            <h1 className="text-3xl font-bold">User Management</h1>
                            <p className="text-muted-foreground">Manage user registrations and permissions</p>
                        </div>
                        <div className="flex items-center space-x-2">
                            <Checkbox id="show-rejected" checked={showRejected} onCheckedChange={(checked) => setShowRejected(checked === true)} />
                            <Label htmlFor="show-rejected" className="cursor-pointer">
                                Show rejected users
                            </Label>
                        </div>
                    </div>
                </div>

                {flash?.success && (
                    <Alert>
                        <AlertDescription>{flash.success}</AlertDescription>
                    </Alert>
                )}

                {flash?.error && (
                    <Alert variant="destructive">
                        <AlertDescription>{flash.error}</AlertDescription>
                    </Alert>
                )}

                <Card>
                    <CardHeader>
                        <CardTitle>All Users</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="divide-y">
                            {filteredUsers.map((user) => (
                                <UserRow
                                    key={user.id}
                                    user={user}
                                    onApprove={handleApprove}
                                    onReject={setRejectingUser}
                                    onToggleAdmin={handleToggleAdmin}
                                    approveProcessing={approveForm.processing}
                                    toggleAdminProcessing={toggleAdminForm.processing}
                                />
                            ))}
                        </div>
                    </CardContent>
                </Card>
            </div>

            <SheetPanel
                open={!!rejectingUser}
                onOpenChange={(open) => {
                    if (!open) closeRejectPanel();
                }}
                title="Reject User"
                footer={
                    <>
                        <Button variant="outline" onClick={closeRejectPanel}>
                            Cancel
                        </Button>
                        <Button variant="destructive" onClick={handleReject} disabled={!rejectForm.data.reason.trim() || rejectForm.processing}>
                            {rejectForm.processing ? 'Rejecting...' : 'Reject'}
                        </Button>
                    </>
                }
            >
                <div className="space-y-2">
                    <Label htmlFor="reason">Rejection Reason</Label>
                    <Textarea
                        id="reason"
                        value={rejectForm.data.reason}
                        onChange={(e) => rejectForm.setData('reason', e.target.value)}
                        placeholder="Enter reason for rejection..."
                    />
                    {rejectForm.errors.reason && <p className="text-sm text-red-500">{rejectForm.errors.reason}</p>}
                </div>
            </SheetPanel>
        </AdminLayout>
    );
}
