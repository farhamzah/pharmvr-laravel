import { 
  MoreVertical, 
  Search, 
  Plus, 
  Filter,
  CheckCircle2,
  Clock,
  XCircle
} from 'lucide-react';
import styles from './users.module.css';

const users = [
  { id: 1, name: 'Dr. Sarah Connor', email: 'sarah.c@hospital.com', role: 'Physician', status: 'Active', lastActive: '2 mins ago' },
  { id: 2, name: 'John Doe', email: 'john.doe@patient.com', role: 'Patient', status: 'Active', lastActive: '15 mins ago' },
  { id: 3, name: 'Marcus Wright', email: 'marcus.w@tech.com', role: 'Specialist', status: 'Pending', lastActive: 'Inactive' },
  { id: 4, name: 'Kyle Reese', email: 'kyle.r@unit.com', role: 'Patient', status: 'Blocked', lastActive: '2 days ago' },
  { id: 5, name: 'T-800 Model', email: 'unit.800@cyber.com', role: 'Admin', status: 'Active', lastActive: '1 hour ago' },
];

export default function UserManagementPage() {
  return (
    <div className={styles.container}>
      <div className={styles.header}>
        <div>
          <h1 className={styles.title}>User Management</h1>
          <p className={styles.subtitle}>Manage clinical staff, patients, and administrative accounts.</p>
        </div>
        <button className="btn-primary" style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
          <Plus size={18} />
          <span>Add User</span>
        </button>
      </div>

      <div className={`${styles.toolbar} card`}>
        <div className={styles.searchBox}>
          <Search size={18} className={styles.searchIcon} />
          <input type="text" placeholder="Search by name, email or role..." />
        </div>
        <div className={styles.filters}>
          <button className={styles.filterBtn}>
            <Filter size={18} />
            <span>Filters</span>
          </button>
          <select className={styles.select}>
            <option>All Roles</option>
            <option>Physician</option>
            <option>Patient</option>
            <option>Admin</option>
          </select>
        </div>
      </div>

      <div className={`${styles.tableWrapper} card`}>
        <table className={styles.table}>
          <thead>
            <tr>
              <th>User</th>
              <th>Role</th>
              <th>Status</th>
              <th>Last Active</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            {users.map((user) => (
              <tr key={user.id}>
                <td>
                  <div className={styles.userInfo}>
                    <p className={styles.userName}>{user.name}</p>
                    <p className={styles.userEmail}>{user.email}</p>
                  </div>
                </td>
                <td>
                  <span className={styles.roleTag}>{user.role}</span>
                </td>
                <td>
                  <div className={`${styles.statusBadge} ${styles[user.status.toLowerCase()]}`}>
                    {user.status === 'Active' && <CheckCircle2 size={14} />}
                    {user.status === 'Pending' && <Clock size={14} />}
                    {user.status === 'Blocked' && <XCircle size={14} />}
                    <span>{user.status}</span>
                  </div>
                </td>
                <td className={styles.lastActive}>{user.lastActive}</td>
                <td>
                  <button className={styles.actionBtn}><MoreVertical size={18} /></button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}
