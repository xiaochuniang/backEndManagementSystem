export interface MenuOption {
  index: string
  title: string
  icon?: string
  children?: MenuOption[]
  hidden?: boolean
}
