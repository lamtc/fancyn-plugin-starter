import autoprefixer from 'autoprefixer';
import prefixSelector from 'postcss-prefix-selector';
import tailwindcss from 'tailwindcss';

export default {
  plugins: [
    tailwindcss(),
    autoprefixer(),
    prefixSelector({
      prefix: '.fps-plugin-root',
      transform(prefix, selector, prefixedSelector) {
        return selector.includes(prefix) ? selector : prefixedSelector;
      },
    }),
  ],
};
