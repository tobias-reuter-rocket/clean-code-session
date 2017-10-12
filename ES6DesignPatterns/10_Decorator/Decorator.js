

class Hero {
  hit() {
    console.log('Punch!');
  }
}

function badassery(hero) {
  let fn = hero.hit;
  hero.hit = () => {
    console.log('"Show\'s over, mo*******rs!"');
    fn();
  };
}

const hitGirl = new Hero('Hit-Girl');
badassery(hitGirl);
hitGirl.hit();
// "Show's over, mo*******rs!"
// Punch!
