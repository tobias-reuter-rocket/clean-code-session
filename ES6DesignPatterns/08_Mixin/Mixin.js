
class Human {
  // ...
}

class Hero extends Human {
  // ...
}

const Fighter = (target) =>
  Object.assign(target, {
    hit() {
      console.log('Hit with fist.');
    }
  });
Fighter(Hero.prototype);

const Armed = (target) =>
  Object.assign(target, {
    hitWithTaser() {
      console.log('Tzzzzt!');
    },
    hitWithBaton() {
      console.log('Swoosh!');
    },
  });
Armed(Hero.prototype);

const kickAss = new Hero('Kick-Ass');
kickAss.hit();          // Hit with fist.
kickAss.hitWithTaser(); // Tzzzzt!
kickAss.hitWithBaton(); // Swoosh!


