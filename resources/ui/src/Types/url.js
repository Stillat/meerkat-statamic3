class Url {

  static toAbsolute(base: string, relative: string): string {
    let stack = base.split('/'),
      parts = relative.split('/');

    stack.pop();

    for (let i = 0; i < parts.length; i += 1) {
      if (parts[i] === '.') {
        continue;
      }

      if (parts[i] === '..') {
        stack.pop();
      } else {
        stack.push(parts[i]);
      }
    }

    return stack.join('/');
  }

  static current() {
    return window.location.href;
  }

  static currentLastValue() {
    return Url.lastValue(Url.current());

  }

  static lastValue(url: string) {
    let parts = url.split('?'),
      nonParamPart = parts[0].split('/');

    if (nonParamPart.length === 0) {
      return '';
    }

    return nonParamPart[nonParamPart.length - 1];
  }

}

export default Url;
