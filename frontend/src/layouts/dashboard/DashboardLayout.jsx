import { useEffect } from 'react';
import { Outlet } from 'react-router-dom';
import DashboardHeader from './header/DashboardHeader';
import NavbarVertical from './navbar/NavbarVertical';
import useGlobalStore from '../../stores/useGlobalStore';

export default function DashboardLayout() {
  const fetchEtapasActivas = useGlobalStore((state) => state.fetchEtapasActivas);

  useEffect(() => {
    fetchEtapasActivas();
  }, [fetchEtapasActivas]);

  return (
    <>
      <DashboardHeader />
      <div className="layout">
        <NavbarVertical />
        <div className="content">
          <Outlet />
        </div>
      </div>
    </>
  );
}
