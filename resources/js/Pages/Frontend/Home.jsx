import GuestLayout from '@/Layouts/Frontend/GuestLayout';
import { Head, Link } from '@inertiajs/react';

//Components:
import NavLink from '@/Components/NavLink';

//Hooks:
import { useTranslation } from '@/Hooks/useTranslation';

export default function Home(){
    const __ = useTranslation();
    
    return(
        <GuestLayout>
            <Head title='Home' />

            <a href={route('login')}
                className={`block mr-3 px-2 py-1 rounded hover:bg-gray-100 text-sm`}
            >
                Login
            </a>

            <a href={route('register')}
                className={`block px-2 py-1 rounded hover:bg-gray-100 text-sm`}
            >
                Nueva cuenta
            </a>
            <br /><br />

            Bienvenidos a Coopmunidad
        </GuestLayout>
    );
}