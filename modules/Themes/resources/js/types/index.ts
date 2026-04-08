export type Font = {
  family: string;
  category: string;
  variants: string[];
  variable?: boolean;
};

export type Unit = {
  min?: number;
  max?: number;
  step?: number;
  unit?: 'px' | 'rem' | 'em' | '%' | ''; 
}

type ThemeFieldConfig = {
  unit: Unit;
  font: Font;
  // Using object here to allow for future extensibility (e.g. different color formats)
  color: object;
  select: { options: Array<{ value: string; label: string }> };
};

type BaseThemeField = {
  key: string;
  label: string;
  vars: string[];
  group?: FieldGroup;
  /** Unit fields that legitimately vary per mode (e.g. shadow-opacity).
   *  When true, toJson() writes them to light/dark instead of the theme section. */
  perMode?: boolean;
  binding?: {
    [key: string]: (value: unknown) => void;
  };
};

export type FieldGroup = {
  name: string;
  description?: string;
  collapsed?: boolean;
  syncable?: boolean;
};

export interface Theme {
  id: string;
  name: string;
  description: string;
  light: Record<string, string>;
  dark: Record<string, string>;
  editable: boolean;
}

/** Keyed by ThemeField.key */
export type ThemeOverrides = Record<string, string>;

export type ThemeField = {
  [K in keyof ThemeFieldConfig]: BaseThemeField & {
    type: K;
    props?: ThemeFieldConfig[K];
  };
}[keyof ThemeFieldConfig];

/** ThemeField with its current runtime value — used inside ThemePanel */
export type FieldState = ThemeField & { value: string };
