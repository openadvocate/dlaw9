/**
 * @file
 * Global utilities.
 *
 */
(function($, Drupal, once) {
  "use strict";

  Drupal.behaviors.topicsCategoryLayout = {
    attach: function(context, settings) {
      if ($(".topics-category__rhs").length) {
        const cnt = $(".topics-category__topics").length;

        if (cnt === 2) {
          $(".topics-category__bg").addClass("couple");
        } else if (cnt >= 3) {
          $(".topics-category__bg").addClass("multi");
        }
      }
    },
  };

  Drupal.behaviors.pageLastUpdated = {
    attach: function(context, settings) {
      if ($(".last-updated-date").length) {
        once("lastUpdateDate", ".last-updated-date", context).forEach(
          function() {
            $(".last-updated-date")
              .clone()
              .addClass("cloned")
              .appendTo($(".page__lhs"));
          }
        );
      }
    },
  };

  Drupal.behaviors.bannerCtaCircleHeight = {
    attach: function(context, settings) {
      if ($(window).width() < 768) {
        if ($(".banner__center-circle").length) {
          $(".banner__center-container").each(function() {
            $(".banner__center-circle", this).height(
              $(this).outerHeight() + 20
            );
          });
        }
      }
    },
  };

  Drupal.behaviors.bootstrap_barrio = {
    attach: function(context, settings) {
      var position = $(window).scrollTop();
      $(window).on("scroll", function() {
        if ($(this).scrollTop() > 50) {
          $("body").addClass("scrolled");
        } else {
          $("body").removeClass("scrolled");
        }
        var scroll = $(window).scrollTop();
        if (scroll > position) {
          $("body").addClass("scrolldown");
          $("body").removeClass("scrollup");
        } else {
          $("body").addClass("scrollup");
          $("body").removeClass("scrolldown");
        }
        position = scroll;
      });

      var toggleAffix = function(affixElement, scrollElement, wrapper) {
        var height = affixElement.outerHeight(),
          top = wrapper.offset().top;
        if (scrollElement.scrollTop() >= top) {
          wrapper.height(height);
          affixElement.addClass("affix");
        } else {
          affixElement.removeClass("affix");
          wrapper.height("auto");
        }
      };
      $('[data-toggle="affix"]').each(function() {
        var ele = $(this),
          wrapper = $("<div></div>");
        ele.before(wrapper);
        $(window).on("scroll resize", function() {
          toggleAffix(ele, $(this), wrapper);
        });
        // init
        toggleAffix(ele, $(window), wrapper);
      });
    },
  };

  Drupal.behaviors.mobileMenu = {
    attach: function(context, settings) {
      $(once("mobile-menu", ".mobile-hamburger", context)).on(
        "click",
        function() {
          $(this).toggleClass("active");
          $(".navbar-toggler").trigger("click");
        }
      );

      if ($(window).width() < 1200) {
        $(
          once(
            "secondary-menu",
            ".region-secondary-menu .search__block",
            context
          )
        )
          .clone()
          .addClass("cloned")
          .prependTo($("#block-bootstrap-ui-main-menu"));

        $(once("translation-header", "#block-gtranslate", context))
          .clone()
          .addClass("cloned")
          .attr("id", "block-gtranslate-header-cloned")
          .prependTo($("#block-bootstrap-ui-main-menu"));

        $(once("social-media-header", ".header .social-media-links", context))
          .clone()
          .addClass("cloned")
          .prependTo($(".region-secondary-menu"));

        $(once("region-secondary-menu", ".region-secondary-menu", context))
          .clone()
          .addClass("cloned")
          .appendTo($("#block-bootstrap-ui-main-menu"));

        if ($(window).width() >= 768) {
          $(once("quick-exit-link", ".exit__link", context))
            .clone()
            .addClass("cloned")
            .appendTo($(".main-menu-items-wrap"));
        }
      }
    },
  };

  Drupal.behaviors.toggling = {
    attach: function(context, setting) {
      if ($(".page__accdin").length) {
        $(once("accodion-toggle", ".page__accdin-title", context)).on(
          "click",
          function() {
            $(this).toggleClass("collapsed");
            $(".page__accdin-body", this).slideToggle("slow");
          }
        );
      }
    },
  };

  Drupal.behaviors.pageTableOfContents = {
    attach: function(context, settings) {
      once("pageTableOfContents", ".page__toc", context).forEach(function() {
        if ($(".page__toc").length) {
          const body = document.querySelector(".page__body .field--name-body");
          const headings = body.querySelectorAll("h1, h2, h3, h4, h5, h6");

          // anchoring
          const pageTitle = document.querySelector(".page__title");
          const pageTitleTxt = pageTitle.innerText
            .replace(/[`~!@#$%^&*()_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi, "")
            .replaceAll("-", "")
            .replaceAll(" ", "");
          pageTitle.setAttribute("id", pageTitleTxt);

          setTimeout(() => {
            $(":header").each(function() {
              const name = $(this)
                .text()
                .replace(/[`~!@#$%^&*()_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi, "")
                .replaceAll("-", "")
                .replaceAll(" ", "");

              $(this).attr("id", name);
            });
          }, 1000);

          // append H1
          once("addH1", ".toc__content", context).forEach((item, i) => {
            item.innerHTML +=
              "<li class='toc__content-parent' data-position=" +
              pageTitle.offsetTop +
              "><a href='#" +
              pageTitleTxt +
              "'>" +
              pageTitle.innerText +
              "</a></li>";
          });

          // scroll for TOC
          if ($(window).width() >= 1200) {
            $(window).on("scroll", function() {
              isElementVisible($(".page__scrollarea"), headings);
            });
          }

          const headers = body.querySelectorAll("h2, h3");

          let parentItem;
          let parentItemAnchor;

          once("addToToc", headers, context).forEach((head, index) => {
            if (head.nodeName === "H2") {
              parentItem = head.innerText.replaceAll(" ", "");
              parentItemAnchor = head.innerText
                .replace(/[`~!@#$%^&*()_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi, "")
                .replaceAll("-", "")
                .replaceAll(" ", "");

              $(".toc__content").append(
                "<li class='toc__content-parent' data-position=" +
                  head.offsetTop +
                  "><a href='#" +
                  parentItemAnchor +
                  "'>" +
                  head.innerText +
                  "</a></li>"
              );
            }

            if (index < headers.length - 1) {
              if (
                headers[index + 1].nodeName === "H3" ||
                headers[index + 1].nodeName === "H4"
              ) {
                let next = headers[index + 1];
                const childPos = next.offsetTop;
                const anchorText = next.innerText
                  .replace(/[`~!@#$%^&*()_|+\-=?;:'",.<>\{\}\[\]\\\/]/gi, "")
                  .replaceAll("-", "")
                  .replaceAll(" ", "");

                $(".toc__content").append(
                  "<li class='toc__content-child hidden' data-parent=" +
                    parentItem +
                    " data-position=" +
                    childPos +
                    "><a href='#" +
                    anchorText +
                    "'>" +
                    next.innerText +
                    "</a></li>"
                );
              }
            }
          });
        }

        function removeClass(arr) {
          arr.forEach((ar, index) => {
            arr[index].classList.remove("active");
          });
        }

        function showChildren(parent) {
          const children = document.querySelectorAll(".toc__content-child");

          const parentText = parent.innerText.replaceAll(" ", "");

          children.forEach((child, index) => {
            if (child.dataset.parent === parentText) {
              child.classList.remove("hidden");
            } else {
              child.classList.add("hidden");
            }
          });
        }

        const parents = document.querySelectorAll(".toc__content-parent");
        const offset = 20;

        parents.forEach((parent) => {
          document.addEventListener("scroll", function(e) {
            let currentPos = e.target.scrollingElement.scrollTop;
            if (currentPos >= parent.dataset.position - offset) {
              removeClass(parents);
              parent.classList.add("active");
              showChildren(parent);
            }
          });
        });

        const children = document.querySelectorAll(".toc__content-child");

        children.forEach((child) => {
          document.addEventListener("scroll", function(e) {
            let currentPos = e.target.scrollingElement.scrollTop;

            if (currentPos >= child.dataset.position - offset) {
              removeClass(children);
              child.classList.add("active");
            }
          });
        });
      });
    },
  };

  Drupal.behaviors.contactZipCode = {
    attach: function(context, settings) {
      $(".contact__zip .field__item").each(function(i) {
        if (i >= 14) {
          $(this).addClass("hidden");
          $(".contact__zip-btn").appendTo($(".field--name-field-zipcode"));
          $(".contact__zip-btn").addClass("active");
          $(".contact__zip-btn span").text(
            $(".contact__zip .field__item").length - 13
          );
        }
      });

      $(once("zip-code", ".contact__zip-btn.active", context)).on(
        "click",
        function() {
          $(".contact__zip .field__item").removeClass("hidden");
          $(this).addClass("hidden");
        }
      );
    },
  };

  Drupal.behaviors.facetFilter = {
    attach: function(context, settings) {
      $(once("collapse", ".facet-block__title", context)).on(
        "click",
        function() {
          $(this).toggleClass("active");
          $(this)
            .next()
            .toggleClass("hide");
        }
      );

      if ($(window).width() < 1200) {
        $(once("facet-filter", ".facet-filter-hamburger", context)).on(
          "click",
          function() {
            $(this).addClass("active");
            $(".search-page__rhs").addClass("active");
            // $(".modal-backdrop").addClass("show");
            $(this).modal("show");
          }
        );

        $(once("facet-filter-close", ".facet-filter-close", context)).on(
          "click",
          function() {
            $(".search-page__rhs, .facet-filter-hamburger").removeClass(
              "active"
            );
            $(".facet-filter-hamburger")
              .modal("hide")
              .css("display", "flex");
          }
        );
      }
    },
  };

  // NOTE: we are disabling testimonial carousel for now
  // Currently we are using another version of slick slider
  // that is more accessible for accessibility https://accessible360.github.io/accessible-slick/
  // this version does not support the decimals for slidesToScroll and slidesToShow
  // if you are re-enabling the component in the future make sure to re-implement the way
  // this carousel works
  Drupal.behaviors.testimonialSlider = {
    attach: function(context, settings) {
      $(".testimonial__swiper").slick({
        responsive: [
          {
            breakpoint: 1200,
            settings: {
              slidesToShow: 1,
              slidesToScroll: 1,
              adaptiveHeight: true,
              prevArrow:
                '<button class="slide-arrow prev-arrow">Previous</button>',
              nextArrow: '<button class="slide-arrow next-arrow">Next</button>',
            },
          },
          {
            breakpoint: 9999,
            settings: {
              // slidesToScroll: 1.5,
              slidesToScroll: 1,
              centerMode: true,
              centerPadding: "30px",
              // slidesToShow: 2.3,
              slidesToShow: 2,
              prevArrow:
                '<button class="slide-arrow prev-arrow">Previous</button>',
              nextArrow: '<button class="slide-arrow next-arrow">Next</button>',
            },
          },
        ],
      });
    },
  };

  Drupal.behaviors.slickSlider = {
    attach: function(context, settings) {
      $(".twitter__feed-container").slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        adaptiveHeight: true,
        mobileFirst: true,
        responsive: [
          {
            breakpoint: 767,
            settings: "unslick",
          },
        ],
      });

      if ($(".next-arrow").length && $(window).width() >= 768) {
        $(once("slick-arrow-next", ".next-arrow", context)).appendTo(
          $(".testimonial__top")
        );
      }
      if ($(".prev-arrow").length && $(window).width() >= 768) {
        $(once("slick-arrow-prev", ".prev-arrow", context)).appendTo(
          $(".testimonial__top")
        );
      }
      $(".para-img-txt__wrapper").slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        prevArrow: $(".img-txt-slick-custom-prev"),
        nextArrow: $(".img-txt-slick-custom-next"),
        responsive: [
          {
            breakpoint: 768,
            settings: {
              adaptiveHeight: true,
              dots: false,
            },
          },
          {
            breakpoint: 9999,
            settings: {
              arrows: false,
              dots: true,
              appendDots: $(".slick-custom-dots"),
            },
          },
        ],
      });
      $(".hero__img").slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        prevArrow: $(".hero-slick-custom-prev"),
        nextArrow: $(".hero-slick-custom-next"),
        responsive: [
          {
            breakpoint: 768,
            settings: {
              adaptiveHeight: true,
              dots: false,
            },
          },
          {
            breakpoint: 9999,
            settings: {
              arrows: false,
              dots: true,
              appendDots: $(".hero-slick-custom-dots"),
            },
          },
        ],
      });
    },
  };

  Drupal.behaviors.searchPage = {
    attach: function(context, settings) {
      if (
        $(".facets-widget-checkbox").length &&
        $(".facet-item__value").length
      ) {
        $(".facet-item__value").each(function() {
          if ($(this).text() === "Contact") {
            $(this).text("Get Help");
          } else if ($(this).text() === "Page") {
            $(this).text("Information");
          }
        });
      }
    },
  };

  Drupal.behaviors.exitLink = {
    attach: function(context, settings) {
      $(".exit__link").on("click", function() {
        setTimeout(() => {
          window.location.replace("https://www.google.com");
        }, 500);
      });
    },
  };

  Drupal.behaviors.searchMainHeader = {
    attach: function(context, settings) {
      const form = $("#views-exposed-form-search-search");
      $(
        once("main-search", ".region-secondary-menu .default__btn", context)
      ).on("click", function() {
        form.submit();
      });
    },
  };

  /**
   * Checks to see if the element is inside of the current viewport
   *
   * @param {*} el element to check for
   *
   * @author Jay Lee
   *
   */
  const isElementVisible = (el, arr) => {
    let rect = el[0].getBoundingClientRect(),
      vWidth = window.innerWidth || document.documentElement.clientWidth,
      vHeight = window.innerHeight || document.documentElement.clientHeight,
      efp = function(x, y) {
        return document.elementFromPoint(x, y);
      };

    // Return false if it's not in the viewport
    if (
      rect.right < 0 ||
      rect.bottom < 0 ||
      rect.left > vWidth ||
      rect.top > vHeight
    )
      return false;

    // this will follow the user scrolling on page and add active class
    // to the table of content list based on the position of the page

    if (rect.top < 0) {
      $(".page__toc")
        .css("position", "absolute")
        .css("top", -rect.top + 100);

      if (
        parseInt($(".page__toc").css("top")) >=
        parseInt($(".page__scrollarea").height()) - $(".page__toc").height()
      ) {
        $(".page__toc").css(
          "top",
          parseInt($(".page__scrollarea").height()) - $(".page__toc").height()
        );
        return false;
      }
    } else {
      $(".page__toc")
        .css("position", "unset")
        .css("top", 0);
    }

    // Return true if any of its four corners are visible
    return (
      el[0].contains(efp(rect.left, rect.top)) ||
      el[0].contains(efp(rect.right, rect.top)) ||
      el[0].contains(efp(rect.right, rect.bottom)) ||
      el[0].contains(efp(rect.left, rect.bottom))
    );
  };
})(jQuery, Drupal, once);
