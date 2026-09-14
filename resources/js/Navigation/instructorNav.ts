export const instructorNav = () => [
    { label: 'Dashboard', href: route('instructor.dashboard') },
    { label: 'My courses', href: route('admin.courses.index') },
    { label: 'Assignments to grade', href: route('instructor.assignments.index') },
];
