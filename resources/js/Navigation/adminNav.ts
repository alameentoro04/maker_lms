/** Shared nav for every Admin/* page — extend here as new admin sections land. */
export const adminNav = () => [
    { label: 'Dashboard', href: route('admin.dashboard') },
    { label: 'Courses', href: route('admin.courses.index') },
    { label: 'Categories', href: route('admin.categories.index') },
    { label: 'Cohorts', href: route('admin.cohorts.index') },
    { label: 'Enrollments', href: route('admin.enrollments.index') },
];
