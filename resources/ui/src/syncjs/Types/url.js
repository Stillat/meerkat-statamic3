class Url {

  static toAbsolute(base : string, relative : string) : string {
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

}

export default Url;
