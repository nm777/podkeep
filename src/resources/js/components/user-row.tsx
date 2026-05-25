import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

interface User {
    id: number;
    name: string;
    email: string;
    is_admin: boolean;
    approval_status: 'pending' | 'approved' | 'rejected';
    created_at: string;
}

function getStatusBadge(status: string) {
    const variants = {
        pending: 'secondary',
        approved: 'default',
        rejected: 'destructive',
    } as const;

    return <Badge variant={variants[status as keyof typeof variants] || 'secondary'}>{status.charAt(0).toUpperCase() + status.slice(1)}</Badge>;
}

interface UserRowProps {
    user: User;
    onApprove: (user: User) => void;
    onReject: (user: User) => void;
    onToggleAdmin: (user: User) => void;
    approveProcessing: boolean;
    toggleAdminProcessing: boolean;
}

export default function UserRow({ user, onApprove, onReject, onToggleAdmin, approveProcessing, toggleAdminProcessing }: UserRowProps) {
    return (
        <div className="py-3">
            <div className="flex items-start justify-between gap-2">
                <div className="min-w-0">
                    <p className="font-medium">{user.name}</p>
                    <p className="text-sm text-muted-foreground">{user.email}</p>
                </div>
                <div className="flex shrink-0 items-center gap-1.5">
                    {getStatusBadge(user.approval_status)}
                    <Badge variant={user.is_admin ? 'default' : 'secondary'}>{user.is_admin ? 'Admin' : 'User'}</Badge>
                </div>
            </div>
            <div className="mt-1 text-xs text-muted-foreground">Joined {new Date(user.created_at).toLocaleDateString()}</div>
            <div className="mt-2 flex flex-wrap gap-2">
                {user.approval_status !== 'approved' && (
                    <Button size="sm" onClick={() => onApprove(user)} disabled={approveProcessing}>
                        {approveProcessing ? 'Approving...' : 'Approve'}
                    </Button>
                )}
                {user.approval_status !== 'rejected' && (
                    <Button size="sm" variant="destructive" onClick={() => onReject(user)}>
                        Reject
                    </Button>
                )}
                {user.approval_status === 'rejected' && (
                    <Button size="sm" variant="outline" onClick={() => onReject(user)}>
                        Reject (update reason)
                    </Button>
                )}
                <Button size="sm" variant="outline" onClick={() => onToggleAdmin(user)} disabled={toggleAdminProcessing}>
                    {toggleAdminProcessing ? 'Updating...' : user.is_admin ? 'Remove Admin' : 'Make Admin'}
                </Button>
            </div>
        </div>
    );
}
