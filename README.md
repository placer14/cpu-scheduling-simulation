# CPU Scheduler Simulation

[![CC BY-NC-SA 3.0][cc-by-nc-sa-shield]][cc-by-nc-sa]

To properly illustrate the functionality of a CPU scheduling algorithm and the effect each algorithm has on the execution of processes, a CPU scheduling simulator was written using PHP and the results of three algorithms were collected and compared. The type of algorithm used often impacts the apparent speed at which the processes perform and complete.

The report includes a comparison of results using the “First Come First Serve” and “Multilevel Feedback” algorithms are compared with pre-generated results of the “Shortest Job First” algorithm. We identify the strengths and weaknesses of each algorithm and discuss potential applications where each algorithm may be best suited. Additionally, we will consider the efficiency of the simulation project and discuss pitfalls of its implementation and potential areas of improvement for future iterations.

## Run using Docker

The simulation may be executed locally using the provided Dockerfile like this:

```
docker build -t cpuscheduler .
docker run --rm -it -p 8080:80 cpuscheduler
```

The simulation can then be interacted with by pointing your web browser at `http://localhost:8080`.

## License

This work is licensed under a
[Creative Commons Attribution-NonCommercial-ShareAlike 3.0 International License][cc-by-nc-sa].

[cc-by-nc-sa]: http://creativecommons.org/licenses/by-nc-sa/3.0/
[cc-by-nc-sa-shield]: https://img.shields.io/badge/License-CC%20BY--NC--SA%203.0-lightgrey.svg
