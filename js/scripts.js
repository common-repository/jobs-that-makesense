function jobsDocReady(fn) {
  // see if DOM is already available
  if (
    document.readyState === "complete" ||
    document.readyState === "interactive"
  ) {
    // call on next available tick
    setTimeout(fn, 1);
  } else {
    document.addEventListener("DOMContentLoaded", fn);
  }
}

jobsDocReady(function () {
  const modal = document.getElementById("jobs-that-makesense_modal");

  function jobsOpenModal(id) {
    document.getElementById("jobs-that-makesense_modal-iframe").src =
      "https://jobs.makesense.org/apply?id=" + id + "&context=iframe&utm_source=wordpress-integration&utm_campaign=" + location.hostname;
    modal.classList.add("open");
  }

  /*
  document.getElementById("jobs-that-makesense_modal-iframe").src =
    "https://jobs.makesense.org/loading";*/

  const exits = modal.querySelectorAll(".jobs-that-makesense_modal-exit");
  exits.forEach(function (exit) {
    exit.addEventListener("click", function (event) {
      event.preventDefault();
      document.getElementById("jobs-that-makesense_modal-iframe").src =
        "https://jobs.makesense.org/loading?context=iframe&utm_source=wordpress-integration&utm_campaign=" + location.hostname;
      modal.classList.remove("open");
    });
  });

  const matches = document.querySelectorAll(".jobs-that-makesense_list .job");
  for (let i = 0; i < matches.length; i++) {
    matches[i].addEventListener(
      "click",
      function (event) {
        var link = event.target;
        if (event.target !== matches[i]) link = event.target.closest("a.job");

        if (link.dataset.redirection === "inactive") {
          if (typeof window.history.pushState == "function") {
            history.pushState({}, "", "?job=" + link.dataset.id);
          }
          jobsOpenModal(link.dataset.id);

          event.stopPropagation();
          event.preventDefault();
          return false;
        }
      },
      false
    );
  }

  const params = new Proxy(new URLSearchParams(window.location.search), {
    get: (searchParams, prop) => searchParams.get(prop),
  });

  if (params.job) {
    jobsOpenModal(params.job);
  }

  Array.from(document.querySelectorAll('.jobs-that-makesense_list')).forEach((listContainer) => {
    const isChildren = listContainer.classList.contains('jobs-that-makesense_children_list');

    const id = listContainer.getAttribute('data-id');
    const paginate = listContainer.getAttribute('data-paginate');
    const fields = listContainer.getAttribute('data-fields');
    const childJobs = listContainer.getAttribute('data-child-jobs');
    const featured = listContainer.getAttribute('data-featured');

    let currentPage = 1;
    let loading = false;
    listContainer.addEventListener('click', async (event) => {
      if (loading) {
        return;
      }
      if (event.target.tagName === 'A' && event.target.parentElement.classList.contains('jobs-that-makesense_pagination')) {
        event.preventDefault();
        loading = true;
        listContainer.classList.add('--loading');
        const params = new URLSearchParams();
        params.append('action', isChildren ? 'jtms_children_pagination' : 'jtms_jobs_pagination');
        params.append('id', id);
        params.append('paginate', paginate);
        params.append('fields', fields);
        params.append('child-jobs', childJobs);
        params.append('featured', featured);
        if (event.target.classList.contains('next')) {
          currentPage++;
        }
        else if (event.target.classList.contains('prev')) {
          currentPage--;
        }
        else {
          currentPage = parseInt(event.target.innerText.trim());
        }
        params.append('page', currentPage);
        const response = await fetch(jobs_that_makesense_ajax_object.ajaxurl + '?' + params.toString());
        const paginateHTML = await response.text();
        listContainer.innerHTML = paginateHTML;
        listContainer.scrollIntoView({behavior: 'smooth'});
        setTimeout(() => {
          listContainer.classList.remove('--loading');
          loading = false;
        }, 500);
      }
    });
  });
});
