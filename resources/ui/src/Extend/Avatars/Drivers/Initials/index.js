import template from './template.html';

let avatarColors = {
  'a': ['#f6e58d', '#000000'],
  'b': ['#34ace0', '#ffffff'],
  'c': ['#ff5252', '#ffffff'],
  'd': ['#6ab04c', '#ffffff'],
  'e': ['#30336b', '#ffffff'],
  'f': ['#130f40', '#ffffff'],
  'g': ['#686de0', '#ffffff'],
  'h': ['#f9ca24', '#000000'],
  'i': ['#ffbe76', '#000000'],
  'j': ['#2980b9', '#ffffff'],
  'k': ['#badc58', '#ffffff'],
  'l': ['#40407a', '#ffffff'],
  'm': ['#ffb142', '#ffffff'],
  'n': ['#b33939', '#ffffff'],
  'o': ['#f7f1e3', '#ffffff'],
  'p': ['#d1ccc0', '#000000'],
  'q': ['#3742fa', '#ffffff'],
  'r': ['#747d8c', '#ffffff'],
  's': ['#ffa502', '#ffffff'],
  't': ['#2ed573', '#ffffff'],
  'u': ['#70a1ff', '#ffffff'],
  'v': ['#f1f2f6', '#000000'],
  'w': ['#9c88ff', '#ffffff'],
  'x': ['#4cd137', '#ffffff'],
  'y': ['#7b1fa2', '#ffffff'],
  'z': ['#ffa000', '#ffffff'],
  '1': ['#d81b60', '#000000'],
  '2': ['#5e35b1', '#000000'],
  '3': ['#7cb342', '#000000'],
  '4': ['#474787', '#ffffff'],
  '5': ['#227093', '#ffffff'],
  '6': ['#84817a', '#ffffff'],
  '7': ['#218c74', '#ffffff'],
  '8': ['#ffda79', '#000000'],
  '9': ['#f7d794', '#000000'],
  '0': ['#f3a683', '#000000']
};

export default {
  template: template,
  props: {
    author: {
      type: Object,
      default: null
    },
    isRounded: {
      type: Boolean,
      default: true
    }
  },
  data() {
    return {
      backgroundColor: '#ffffff',
      fontColor: '#000000'
    };
  },
  methods: {
    getColors() {
      if (this.avatar === null || this.author.initials.trim().length === 0) {
        return {
          backgroundColor: '#ffffff',
          fontColor: '#000000'
        };
      }

      let foundColors = avatarColors[this.author.initials.trim()[0].toLowerCase()],
        backgroundColor = '#ffffff',
        fontColor = '#000000';

      if (typeof foundColors !== 'undefined') {
        backgroundColor = foundColors[0];
        fontColor = foundColors[1];
      }

      return {
        backgroundColor: backgroundColor,
        fontColor: fontColor
      };
    }
  },
  created() {
    let colors = this.getColors();

    this.backgroundColor = colors.backgroundColor;
    this.fontColor = colors.fontColor;
  }
};
