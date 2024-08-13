import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';

dayjs.extend(relativeTime);

const format = {
  date: (time) => dayjs(time).format('DD MMM YYYY'),
  sysDate: (time) => dayjs(time).format('YYYY-MM-DD'),
  time: (time) => dayjs(time).format('HH:mm'),
  datetime: (time) => dayjs(time).format('DD MMM YYYY, HH:mm'),
  dateDiff: (time) => dayjs().to(dayjs(time)),
  links: (content) => {
    return content.replace(/([^>"]|^)(https:\/\/[^s+]+)([^<"]?)/g, '$1<a href="$2" target="_blank">$2</a>$3')
      .replace('<a href=', '<a class="text-primary" href=');
  },
};

export default {
  install(app) {
    /* eslint-disable  no-param-reassign */
    app.config.globalProperties.$format = format;
  },
};
