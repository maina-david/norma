import { debounce } from 'lodash';
import comments from './comments';
import system from './system';

window.Norma = {};
window.Norma.comments = comments;
window.Norma.system = system;
window._debounce = debounce;
