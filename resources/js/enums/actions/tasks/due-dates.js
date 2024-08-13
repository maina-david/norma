import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';

dayjs.extend(relativeTime);

const DueDates = {
  overdue: 'Overdue',
  today: 'Today',
  tomorrow: 'Tomorrow',
  upcoming: 'Upcoming',
  future: 'Future',
  unset: 'No Due date',

  forGrouping(field) {
    const format = (item) => item.format('YYYY-MM-DD');

    return [
      { value: [`${field},lt,${format(dayjs().subtract(1, 'day'))}`], label: this.overdue },
      { value: [`${field},eq,${format(dayjs())}`], label: this.today },
      { value: [`${field},eq,${format(dayjs().add(1, 'day'))}`], label: this.tomorrow },
      { value: [`${field},gt,${format(dayjs().add(1, 'day'))}`, `${field},lte,${format(dayjs().add(2, 'week'))}`], label: this.upcoming },
      { value: [`${field},gt,${format(dayjs().add(2, 'week'))}`], label: this.future },
      { value: [`${field},eq,null`], label: this.unset },
    ];
  },
};

export default DueDates;
