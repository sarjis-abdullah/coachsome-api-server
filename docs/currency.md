# Promo Code

Here we describe the business logic of promo code.

## TOC

1. [Introduction](#Durations)
2. [Issue](#Issue)

## Introduction

We get currency rate from

[Exchange rate](https://exchangerate.host)


## Our currency

We should have to keep amount data in coachsome database only one currency. It is a simplistic and headless way. We convert our currency according to our base currency. For coachsome it should be like

[Currency Rates](https://api.exchangerate.host/latest?base=DKK)

[History Currency Rates](https://api.exchangerate.host/2021-12-24?base=DKK)

