import { registerGlobalComponent } from '@/lib/globalComponents';
import '../css/app.css';
import ThemePanel from './components/ThemePanel.vue';

export function setup() {
    registerGlobalComponent('top', ThemePanel);
}

export function afterMount() {}
