import dayjs from 'dayjs'

export function formatDate(date: string | number | Date, template = 'YYYY-MM-DD HH:mm:ss'): string {
  return dayjs(date).format(template)
}

export function formatDay(date: string | number | Date): string {
  return dayjs(date).format('YYYY-MM-DD')
}

export function fromNow(date: string | number | Date): string {
  return dayjs(date).fromNow()
}
