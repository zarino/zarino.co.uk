---
layout: post
title: Wrapping your head round the Monty Hall problem
summary: >
  In which Zarino finally understands the famous probability puzzle, and explains his method for working it out.
image: /media/monty-hall.jpg
related:
  - /post/teaching-everyone-to-hack/
  - /post/whats-in-a-checkbox/
---

![Monty Hall](/media/monty-hall.jpg)

There's a famous probability puzzle called the [Monty Hall problem](https://en.wikipedia.org/wiki/Monty_Hall_problem). It is based on an [American TV show](https://en.wikipedia.org/wiki/Let's_Make_a_Deal) where the host, Monty Hall, reveals three doors – one hiding a car, and the other two hiding booby prizes (goats). Monty lets contestants pick one of the three doors, then opens one of the other doors (to reveal a goat), and often then lets the contestant switch their initial choice.

The question is: given the choice, should you switch?

![Monty Hall reveals the three doors on “Let’s make a deal”](/media/monty-hall-doors.jpg)

Almost everybody says “no, it makes no difference – it’s now a decision between two doors, one has a goat, one has a car, so it’s 50/50. I may as well stick with my initial choice, because, if I don’t, I’ll kick myself.”

But this isn’t entirely true. If you play the game out again and again, switching will win you the car 66% of the time, whereas sticking with your initial choice will win the car only 33% of the time. It seems completely counter-intuitive, but the answer is: Yes, you should switch.

![A goat is revealed on “Let’s make a deal”](/media/monty-hall-goat.jpg)

I first heard about this problem last Summer, at [ScraperWiki](http://scraperwiki.com). And despite being in a room packed with mathematicians, scientists and PhDs—and despite [Morty](https://twitter.com/morty_uk)’s efforts to make the puzzle more approachable by replacing the doors with boxes of cupcakes—I still didn’t get what was going on. The voice in my head was saying exactly what I’ve said above: “It’s now a decision between two doors, one has a goat, one a car, so the chances are 50/50.”

They tried explaining it to me by saying, “What if there were 1,000,000 doors, you picked one, and we opened all but one other door – you’d switch to it pretty fast, wouldn’t you? Out of 1,000,000 doors, what’re the chances that you picked the right one up front?” To which my answer was, “Pretty slim, but it doesn’t matter, there are still only two doors, so it’s a 50/50 chance.”

I’ve heard the million door thing used elsewhere too, when someone else has been the poor dupe trying to get their head around the problem. And generally it doesn’t help them either.

---

So for the best part of a year, I saw the Monty Hall problem like one of those maths facts you just learn by rote as a kid. No point working out how it works, just learn the answer and regurgitate it.

Then, a few days ago, I heard an explanation that cuts the crap and finally makes sense:

1. The chance of a goat being behind your initial door is 66%.
2. Because there are only three doors, and Monty always takes one of the two hidden goats out of the equation, all that remains is a goat and a car. You already picked one of them. So if you switch, you’re guaranteed the opposite of your original choice.
3. If you don’t switch, you have that same original 66% chance of getting a goat.
4. If you *do* switch, however, you’re guaranteed the opposite, and therefore a 66% chance of getting the car.

There. Simple. I don’t know why, but that just makes sense to me, in a way no other explanation does. So I’m putting it on the Internet, in case anybody else comes looking, as I did.

![By jove, I think he’s GOAT it](/media/goat-it.jpg)
