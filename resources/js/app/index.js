import { debounce } from 'lodash';
import comments from './comments';
import system from './system';

window.Libryo = {};
window.Libryo.comments = comments;
window.Libryo.system = system;
window._debounce = debounce;
