/**
 * Oho !
 * @author Olivier Le Diouris
 */
const NO_DIALOG_MESSAGE = "Dialog tag not supported.";
const DEFAULT_LANG = "FR";
let currentLang = DEFAULT_LANG; // Init value

let showAboutDialog = () => {
    let aboutDialog = document.getElementById("about-dialog");
    if (aboutDialog.show !== undefined) {
        aboutDialog.style.display = 'inline';
        aboutDialog.show();
    } else {
      alert(NO_DIALOG_MESSAGE);
      aboutDialog.style.display = 'inline';
    }
};

// let aboutDialogOpened = true;

let closeAboutDialog = () => {
    let aboutDialog = document.getElementById("about-dialog");
    // If welcome content
    if (aboutDialog.querySelectorAll('.welcome-message').length > 0) {
        // aboutDialog.style.transitionProperty = 'height';
        // aboutDialog.style.transitionDuration = '5s';
        // aboutDialog.style.transform = 'rotateX(45deg)';
        let rotAngle = 1;
        let rotWaitTime = 25;
        let rotFunc = (angle) => {
            // console.log(`Angle is now ${angle}`);
            aboutDialog.style.transform = `rotateX(${angle}deg) rotateY(${angle}deg)`;
            angle += 1;
            if (angle <= 90) {
                window.setTimeout(() => {
                    rotFunc(angle);
                }, rotWaitTime);
            } else {
                if (aboutDialog.close !== undefined) {
                    aboutDialog.close();
                    aboutDialog.style.display = 'none';
                } else {
                    // alert(NO_DIALOG_MESSAGE);
                    aboutDialog.style.display = 'none';
                }
                // Reset rotation
                aboutDialog.style.transform = `rotateX(0deg) rotateY(0deg)`;
                // remove class welcome-dialog
                aboutDialog.classList.remove('welcome-dialog');
                // reset title
                let dialogTitle = document.querySelectorAll('.dialog-title'); // By its class
                if (dialogTitle) {
                    dialogTitle[0].innerText = '';
                }
            }
        };
        // window.setTimeout(() => {
            if (rotAngle < 90) {
                rotFunc(rotAngle);
            }
        //}, rotWaitTime);
    } else {
        // debugger;
        // console.log("No Welcome message, Closing.")
        if (aboutDialog.close !== undefined) {
            aboutDialog.close();
            aboutDialog.style.display = 'none';
        } else {
            // alert(NO_DIALOG_MESSAGE);
            aboutDialog.style.display = 'none';
        }
    }
};

let generateNextEvents = () => {
    const HOW_FAR = 6; // 5; // 4; // 2;
    let now = new Date();
    let firstIdx;
    // Find first date index
    for (let idx=0; idx<NEXT_EVENTS.length; idx++) {
        let d = new Date(NEXT_EVENTS[idx].date_to);
        d.setDate(d.getDate() + 1); // Add one day (all day) to the end date.
        if (d.getTime() > now.getTime()) {
            firstIdx = idx;
            break;
        }
    }
    if (firstIdx) {
        let nextMeetings = [];
        for (let i=0; i<HOW_FAR; i++) {
            if (NEXT_EVENTS[[firstIdx + i]]) {
                nextMeetings.push(currentLang === 'FR' ? NEXT_EVENTS[firstIdx + i].content.fr : NEXT_EVENTS[firstIdx + i].content.en);
            }
        }
        return nextMeetings;
    } else {
        let nextMeetings = [];
        nextMeetings.push(currentLang === 'FR' ? "Rien de pr&eacute;vu" : "Nothing scheduled");
        return nextMeetings;
    }
};

/**
 * Can be used for event list, and others (like going directly to a project or boat)
 *
 * @param {*} title like "Welcome!"
 * @param {*} content like "welcome", that will fetch "welcome_FR.html" or "welcome_EN.html"
 */
let showDialogOnLoad = (title, content) => { // Use the about-dialog for message on load
    let aboutDialog = document.getElementById("about-dialog");
    // Add the class to show the spinnaker
    // document.getElementById("spinnaker-bg").classList.add('welcome-dialog');
    aboutDialog.classList.add('welcome-dialog');

    let dialogTitle = document.querySelectorAll('.dialog-title'); // By its class
    let dynamicContentContainer = document.getElementById("dialog-content");

    if (dialogTitle) {
        dialogTitle[0].innerHTML = title; // Can be several dialogs... take the first one.
    }

    let contentName = `${content}_${currentLang}.html`; // Like 'tx-01_FR.html'
    let howManyLines = 0;
    fetch(contentName)
        .then(response => {  // Warning... the NOT_FOUND error lands here, apparently.
            console.log(`Data Response: ${response.status} - ${response.statusText}`);
            if (response.status !== 200) { // There is a problem...
                dynamicContentContainer.innerHTML = generateFetchMessage(contentName, response); // `Fetching ${contentName}...<br/> Data Response: ${response.status} - ${response.statusText}<br/><b>En d&eacute;veloppement...<br/>Disponible prochainement.</b>`;
            } else {
                response.text().then(doc => {
                    console.log(`${contentName} code data loaded, length: ${doc.length}.`);
                    let ne = generateNextEvents(); // Populate the dialog with this list.
                    // Populate event list here (ne)
                    let node = new DOMParser().parseFromString(doc, "text/html");
                    // console.log(node.firstChild.innerHTML); // => <a href="#">Link...
                    let list = node.getElementById('event-list');
                    // Remove all childs
                    while (list.firstChild) {
                        list.removeChild(list.lastChild);
                    }
                    howManyLines = ne.length;
                    console.log(`There are ${ne.length} lines...`);
                    // Add new ones
                    ne.forEach(event => {
						let li = document.createElement('li');
                        li.innerHTML = event;
                        list.appendChild(li);
					});
                    // Remove existing (dummy) chilren
                    while (dynamicContentContainer.firstChild) {
                        dynamicContentContainer.removeChild(dynamicContentContainer.lastChild);
                    }
                    // dynamicContentContainer.innerHTML = node.innerHTML; // <- Nope.
                    // dynamicContentContainer.appendChild(node.getRootNode().querySelectorAll('.welcome-message')[0]); // .firstChild);
                    dynamicContentContainer.appendChild(node.body.childNodes[0]);
                    // console.log("Ha !");
                });
            }
        },
        (error, errmess) => {
            console.log("Ooch");
            let message;
            if (errmess) {
                let mess = JSON.parse(errmess);
                if (mess.message) {
                    message = mess.message;
                }
            }
            console.debug("Failed to get code data..." + (error ? JSON.stringify(error, null, 2) : ' - ') + ', ' + (message ? message : ' - '));
            // Plus tard...
            dynamicContentContainer.innerHTML = generateFetchErrorMessage(contentName, error, errmess);
        });

    // dynamicContentContainer.innerHTML = content;
    // if (DIALOG_OPTION) {
    //     showInfoTxDialog();
    // } else {
    //     dynamicContentContainer.style.display = 'block';
    // }

    if (aboutDialog.show !== undefined) {
        aboutDialog.style.display = 'inline';
        aboutDialog.show();
    } else {
      // alert(NO_DIALOG_MESSAGE);
      aboutDialog.style.display = 'inline';
    }
    // Start counter, to close the dialog after a given time
    const HOW_LONG = 10000;

    let timeout = HOW_LONG + (howManyLines > 2 ? ((howManyLines - 2) * 1000) : 0); // TODO howManyLines is set asynchronously...
    console.log(`Timeout: ${timeout} ms`);
    window.setTimeout(() => {
        if (false) {
            if (aboutDialog.close) {
                aboutDialog.close();
            }
            aboutDialog.style.display = 'none';
        } else {
            if (aboutDialog.style.display !== 'none' && aboutDialog.querySelectorAll('.welcome-message').length > 0) { // Not closed already
                closeAboutDialog();
            }
        }
    }, timeout);
};

let openNav = () => {
    // console.log("Opening Hamburger Menu");
    // alert("Opening Hamburger Menu");
    // let newWidth = getComputedStyle(document.documentElement).width;
    // console.log(`Setting new Width to ${newWidth}`);
	// document.getElementById("side-nav").style.width = newWidth; // getComputedStyle(document.documentElement).getPropertyValue('--expanded-nav-width'); // "450px";
    document.getElementById("side-nav").style.display = 'inline-block';
    // debugger;
};

let closeNav = () => {
	// document.getElementById("side-nav").style.width = "0";
    document.getElementById("side-nav").style.display = 'none';
};

let showSection = (id) => {
	//document.getElementById(id).style.display = 'inline-block';
	document.getElementById(id).style.visibility = 'visible';
	document.getElementById(id).style.opacity = 1;
	document.getElementById(id).style.height = 'auto';
};

let hideSection = (id) => {
	//document.getElementById(id).style.display = 'none';
	document.getElementById(id).style.visibility = 'hidden';
	document.getElementById(id).style.opacity = 0;
	document.getElementById(id).style.height = 0;
};

const COLLAPSED_LINE = "&#9658;&nbsp;";
const EXPANDED_LINE = "&#9660;&nbsp;";

let showHideSection = (obj, id) => {
	let innerSpan = obj.querySelector('.expand-collapse');
	if (document.getElementById(id).style.visibility === 'visible') {  // Then hide it
		innerSpan.innerHTML = COLLAPSED_LINE;
		hideSection(id);
	} else { // Show it
		innerSpan.innerHTML = EXPANDED_LINE;
		showSection(id);
	}
};

var currentContext = "1"; // Default, Home page.

let getQSPrm = (prm) => {
	let value;
	let loc = document.location.toString();
	if (loc.indexOf("?") > -1) {
		let qs = loc.substring(loc.indexOf("?") + 1);
		let prms = qs.split('&');
		for (let i=0; i<prms.length; i++) {
			let nv = prms[i].split('=');
			if (nv.length === 2) {
				if (nv[0] === prm) {
					return nv[1];
				}
			}
		}
	}
	return value;
};

let clack = (origin) => {
    let originId = '';
    if (typeof(origin) === 'string') {
        originId = origin.replace('_', '');
    } else {
        console.log(`clack: Click on ${origin.innerText}, id ${origin.id}`);
        originId = origin.id.replace('_', '');
    }
    currentContext = originId;

    if (originId === '11') {
        makeCode(document.location.href);
        return;
    } else {
        try {
            document.getElementById("qrcode").style.display = 'none'; // Improve this ?..
        } catch (err) {
            console.log(JSON.stringify(err));
        }
    }

	let contentName = `/${originId}_${currentLang}.html`; // From the origin !!
    // Specific content rule(s)
	if (false && originId === "62") {  // Not used... But couyld be.
		contentName = "carrousel.html";
	} else if (originId === "22" || originId === "23") { // Menu 2, special management, see below (ONE page only)
        contentName = `21_${currentLang}.html`; // 21, 22 & 23, same doc, different anchor (hashtag).
	// } else if (originId === "32" || originId === "33") {
    //    contentName = `31_${currentLang}.html`; // 31, 32 & 33, same doc, different anchor (hashtag).
    } else if (originId === "67") {
        contentName = `/7_${currentLang}.html`; // Bypass regular behavior...
    } else if (originId === "68") {
        // Fill out agenda content
    } else if (originId === "10") {
        // Finder! Remove the welcome class ?
        let aboutDialog = document.getElementById("about-dialog");
        if (aboutDialog) {
            aboutDialog.classList.remove('welcome-dialog');
            // reset title
            let dialogTitle = document.querySelectorAll('.dialog-title'); // By its class
            if (dialogTitle) {
                dialogTitle[0].innerText = '';
            }
        }
    } else if (originId === "8") {
        contentName = `/64_${currentLang}.html`; // Bypass regular behavior...
    }
	let contentPlaceHolder = document.getElementById("current-content");

	fetch(contentName)
            .then(response => {  // Warning... the NOT_FOUND error lands here, apparently.
                console.log(`Data Response: ${response.status} - ${response.statusText}`);
				if (response.status !== 200) { // There is a problem...
					contentPlaceHolder.innerHTML = generateFetchMessage(contentName, response); // `Fetching ${contentName}...<br/> Data Response: ${response.status} - ${response.statusText}<br/><b>En d&eacute;veloppement...<br/>Disponible prochainement.</b>`;
				} else {
					response.text().then(doc => {
						console.log(`Code data loaded, length: ${doc.length}.`);
						// Some specific cases here
						/* if (origin.id === "1") { // Move this higher. No need to load 1_xx.html ?..
							document.location.reload();
						} else */
                        if (false && originId === "23") { // Not used, an example of a dialog. Content inserted in the <dialog>
							document.getElementById("dialog-content").innerHTML = doc;
							showAboutDialog();
                        } else if (originId === "10") { // The finder
                            document.getElementById("dialog-content").innerHTML = doc; // '<div>Akeu Coucou</div>';
							showAboutDialog();
						} else {
							contentPlaceHolder.innerHTML = doc;
                            if (false && originId === "22") {  // Not used.
                                showSlides(currentSlide);
                            } else if (originId === "21" || originId === "22" || originId === "23") { // Menu 2, One page only, with anchors.
                                // let nbTry = 0;
                                let scrollToAnchor = () => {
                                    const overflow = document.getElementById('action-container-2');
                                    let hashtag = (originId === "21") ? '01' : ((originId === "22") ? '02' : '03');
                                    const anchor = document.querySelector(`a[name='${hashtag}']`);
                                    anchor.scrollIntoView();

                                    /*
                                    const rectOverflow = overflow.getBoundingClientRect();
                                    const rectAnchor = anchor.getBoundingClientRect();

                                    let scroll_top = rectAnchor.top - rectOverflow.top;
                                    console.log(`rectAnchor.top: ${rectAnchor.top}, rectOverflow.top: ${rectOverflow.top} => ${scroll_top}`);
                                    // Set the scroll position of the overflow container
                                    overflow.scrollTop = scroll_top; // .toFixed(0);  // If remains to zero, check div's height
                                    console.log(`>>> Origin: ${originId}: scrolltop: ${overflow.scrollTop} vs ${scroll_top}`);
                                    */
                                    // 2e couche
                                    if (originId === "21") {
                                        window.scrollTo(0, 0); // Scroll on top, if invoked from a button at the bottom of the page
                                    }
                                    window.scrollBy(0, -92); // 92: menu thickness
                                };
                                window.setTimeout(scrollToAnchor, 100);
                                if (false) {
                                    // 2e couche
                                    if (originId === "21") {
                                        window.scrollTo(0, 0); // Scroll on top, if invoked from a button at the bottom of the page
                                    }
                                }
                                fillOutTheTeam(); // For the random order

                                // console.log("Now scrolling.")
                            // } else if (originId === "31" || originId === "32" || originId === "33") {
                            //     const overflow = document.getElementById('action-container');
                            //     let hashtag = (originId === "31") ? '01' : ((originId === "32") ? '02' : '03');
                            //     const anchor = document.querySelector(`a[name='${hashtag}']`);

                            //     const rectOverflow = overflow.getBoundingClientRect();
                            //     const rectAnchor = anchor.getBoundingClientRect();

                            //     // window.setTimeout(() => {
                            //         // Set the scroll position of the overflow container
                            //         overflow.scrollTop = rectAnchor.top - rectOverflow.top;
                            //         console.log("Now Scrolling !");
                            //     //}, 1000);
                            //     // console.log("Scrolling !");
                            } else {
                                if (originId === "4") {
                                    window.setTimeout(() => {
                                        fillOutFleet(null); // Populate default (full) boat list
                                    }, 500);
                                } else if (originId === "62") {
                                    window.setTimeout(() => {
                                        // debugger;
                                        fillOutActu(null); // Populate default (full) news list
                                    }, 500);
                                } else if (originId === "68") {
                                    window.setTimeout(() => {
                                        fillOutNextEvents(null);
                                    }, 500);
                                } else if (originId === "33") { // Partager, PCC
                                    window.setTimeout(() => {
                                        fillOutFleet(CLUB, "share-container", false); // Populate PCC boat list
                                    }, 500);
                                } // 21, fill out team ?

                                window.scrollTo(0, 0); // Scroll on top, if invoked from a button at the bottom of the page
                            }
						}
					});
				}
            },
            (error, errmess) => {
                console.log("Ooch");
                let message;
                if (errmess) {
                    let mess = JSON.parse(errmess);
                    if (mess.message) {
                        message = mess.message;
                    }
                }
                console.debug("Failed to get code data..." + (error ? JSON.stringify(error, null, 2) : ' - ') + ', ' + (message ? message : ' - '));
				// Plus tard...
				contentPlaceHolder.innerHTML = generateFetchErrorMessage(contentName, error, errmess);
            });

    // Scroll somewhere in the loaded data ?
    if (true) {
        console.log(`clack: Checking for 'where' parameter...`);
        let where = getQueryParameterByName('where'); // An anchor ?
        if (where) {
            window.setTimeout(() => {
                // debugger;
                console.log(`Scrolling to ${where}...`);
                scrollToGivenAnchor(where);
            }, 3000); // Wait a bit for the page to load. TODO Try async ?...
            // scrollToGivenAnchor(where);
        }
    }

}

let specialClack = (clackName) => {
    let originId = '';
    if (typeof(clackName) === 'string') {
        originId = clackName.replace('_', '');
    } else {
        console.log(`clack: Click on ${clackName.innerText}, id ${clackName.id}`);
        originId = clackName.id.replace('_', '');
    }
    currentContext = originId;

	let contentName = `/${originId}_${currentLang}.html`; // From the origin !!

    let contentPlaceHolder = document.getElementById("current-content");

    // TODO Set the content
    let dynamicContentContainer = DIALOG_OPTION ? document.getElementById("dialog-tx-content") : document.getElementById("info-tx");
    let dialogTitle = document.querySelectorAll('.dialog-title'); // dialog-title
    if (dialogTitle && dialogTitle.length > 0) {
        dialogTitle[dialogTitle.length - 1].innerHTML = name; // Can be several dialogs... take the last.
    }

    console.log(`onclick, loading ${contentName}`);
    fetch(contentName)
        .then(response => {  // Warning... the NOT_FOUND error lands here, apparently.
            console.log(`Data Response: ${response.status} - ${response.statusText}`);
            if (response.status !== 200) { // There is a problem...
                dynamicContentContainer.innerHTML = generateFetchMessage(contentName, response); // `Fetching ${contentName}...<br/> Data Response: ${response.status} - ${response.statusText}<br/><b>En d&eacute;veloppement...<br/>Disponible prochainement.</b>`;
            } else {
                response.text().then(doc => {
                    console.log(`${contentName} code data loaded, length: ${doc.length}.`);
                    dynamicContentContainer.innerHTML = doc;
                });
            }
        },
        (error, errmess) => {
            console.log("Ooch");
            let message;
            if (errmess) {
                let mess = JSON.parse(errmess);
                if (mess.message) {
                    message = mess.message;
                }
            }
            console.debug("Failed to get code data..." + (error ? JSON.stringify(error, null, 2) : ' - ') + ', ' + (message ? message : ' - '));
            // Plus tard...
            dynamicContentContainer.innerHTML = generateFetchErrorMessage(contentName, error, errmess); // `<b>${contentName} ${currentLang === 'FR' ? ' introuvable...<br/>Bient&ocirc;t dispo !' : ' not found...<br/>Avai;able soon!'}</b>`;
        });

    // dynamicContentContainer.innerHTML = content;
    if (DIALOG_OPTION) {
        showInfoTxDialog();
    } else {
        dynamicContentContainer.style.display = 'block';
    }
}

let updateMenu = () => { // Multilang aspect, in index.html.

    // Tooltips
    document.querySelectorAll("#_1-label").forEach(elmt =>  elmt.innerHTML = (currentLang === "FR" ? "Retour accueil" : "Back to Home Page"));
    document.querySelectorAll(".this-tooltiptext").forEach(elmt =>  elmt.innerHTML = (currentLang === "FR" ? "Besoin d'aide&nbsp;?" : "Need Help?"));
    document.querySelectorAll("#_7").forEach(elmt =>  elmt.title = (currentLang === "FR" ? "Espace Membres" : "Member Space"));
    document.querySelectorAll("#_7-label").forEach(elmt =>  elmt.innerHTML = (currentLang === "FR" ? "Espace Membres" : "Member Space"));
    document.querySelectorAll("#_8").forEach(elmt =>  elmt.title = (currentLang === "FR" ? "La boutique" : "The store"));
    document.querySelectorAll("#_8-label").forEach(elmt =>  elmt.innerHTML = (currentLang === "FR" ? "La boutique" : "The store"));
    document.querySelectorAll("#_9").forEach(elmt =>  elmt.title = (currentLang === "FR" ? "Outils de Navigation" : "Navigation tools"));
    document.querySelectorAll("#_9-label").forEach(elmt =>  elmt.innerHTML = (currentLang === "FR" ? "Outils de Navigation" : "Navigation tools"));
    document.querySelectorAll("#_9bis").forEach(elmt =>  elmt.title = (currentLang === "FR" ? "La caisse &agrave; glingues" : "Tool Box"));
    document.querySelectorAll("#_9bis-label").forEach(elmt =>  elmt.innerHTML = (currentLang === "FR" ? "La caisse &agrave; glingues" : "Tool Box"));
    document.querySelectorAll("#_10").forEach(elmt =>  elmt.title = (currentLang === "FR" ? "Besoin d'aide ?" : "Need Help?"));
    document.querySelectorAll("#_10-label").forEach(elmt =>  elmt.innerHTML = (currentLang === "FR" ? "Besoin d'aide ?" : "Need Help?"));
    document.querySelectorAll("#_lang-flag-label").forEach(elmt =>  elmt.innerHTML = (currentLang === "FR" ? "Switch to English" : "En fran&ccedil;ais"));

    // Others
    document.querySelectorAll("#home-label").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "Accueil" : "Home"));

    // "_11", Qr Code, no update needed.

	document.querySelectorAll("#_2").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "Qui sommes-nous&nbsp;?&nbsp;" : "Who we are&nbsp;"));
	document.querySelectorAll("#_21").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "Passe-Coque, c'est quoi&nbsp;?" : "Passe-Coque, what's that?"));
	document.querySelectorAll("#_22").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "Notre &eacute;quipe" : "Our team"));
	document.querySelectorAll("#_23").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "Cr&eacute;er du lien" : "Connecting people"));

	document.querySelectorAll("#_3").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "Nos actions&nbsp;" : "Our actions&nbsp;"));
	document.querySelectorAll("#_31").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "Transmettre - <i>Les projets</i>" : "Transmitting - <i>The projects</i>"));
	document.querySelectorAll("#_32").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "R&eacute;nover - <i>Le chantier</i>" : "Refitting - <i>The shipyard</i>"));
	document.querySelectorAll("#_33").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "Partager - <i>Le Club</i>" : "Sharing - <i>The Club</i>"));
	// document.querySelectorAll("#_34").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "Formations" : "Trainings"));
	// document.querySelectorAll("#_35").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "Partenaires" : "Partners"));

	document.querySelectorAll("#_4").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "La flotte&nbsp;" : "The fleet&nbsp;"));
	// document.querySelectorAll("#_41").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "Les bateaux" : "The boats"));     // Unused
	// document.querySelectorAll("#_42").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "Les projets" : "The projects"));  // Unused

	document.querySelectorAll("#_5").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "Nous soutenir&nbsp;" : "Support us&nbsp;"));
	document.querySelectorAll("#_51").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "Adh&eacute;rer" : "Join us"));
	document.querySelectorAll("#_52").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "Faire un don" : "Make a donation"));
	document.querySelectorAll("#_53").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "Investir dans l'Eco-Village" : "Invest in the Eco-Village"));

	document.querySelectorAll("#_6").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "En savoir plus&nbsp;" : "Know more&nbsp;"));
	document.querySelectorAll("#_61").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "Contact" : "Contact"));
	document.querySelectorAll("#_62").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "Actualit&eacute;" : "News"));
	document.querySelectorAll("#_63").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "On parle de nous / Presse" : "We're in the news"));
	document.querySelectorAll("#_64").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "La boutique" : "The store"));
	document.querySelectorAll("#_65").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "Partenaires" : "Partners"));
	// document.querySelectorAll("#_66").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "Charte PCC" : "PCC Chart"));
	document.querySelectorAll("#_67").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "Espace Membres" : "Members Space"));
	document.querySelectorAll("#_68").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "Vie de l'asso" : "Life of the asso"));
	document.querySelectorAll("#_69").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "Outils de communication" : "Communication material"));
	// document.querySelectorAll("#_7").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "Espace Membres" : "Members Space"));
	document.querySelectorAll("#ms_7").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? " Espace Membres" : " Members Space"));
	document.querySelectorAll("#bs_8").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? " La boutique" : " The shop"));
	document.querySelectorAll("#bs_9").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? " Navigation" : " Navigation"));
	document.querySelectorAll("#bs_9bis").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? " Bo&icirc;te &agrave; outils" : " Tool Box"));
    // No 9, Navigation works for both languages
	document.querySelectorAll("#bs_10").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? " Recherche..." : " Search..."));
};

let switchLanguage = () => {
    if (currentLang === "FR") { // Then switch to EN
        document.querySelectorAll("#lang-flag").forEach(flagElement => {
            flagElement.src = "./france.gif";
            flagElement.alt = "Drapeau français";
            flagElement.title = "En français";
        });
        currentLang = "EN";
    } else {
        document.querySelectorAll("#lang-flag").forEach(flagElement => {
            flagElement.src = "us_uk_flag.png"; // "./usa.gif";
            flagElement.alt = "US Flag";
            flagElement.title = "Switch to English";
        });
        currentLang = "FR";
    }
	// Le reste...
	updateMenu();

    // Update currently displayed content
    let newId = `_${currentContext}`;
    let el = document.createElement("div");
	el.id = newId;
	clack(el);

    // clack(el);
};

let generateFetchMessage = (contentName, response) => {
    let mess = (currentLang === 'FR') ? 'Cette page est en cours de d&eacute;veloppement...<br/>Disponible prochainement.' :
                                        'This page is being developped...<br/>Available soon.';
    let message = `<div style='margin: 10px;'><div style='display: none;'>Message :<br/> Fetching ${contentName}...<br/>Data Response: ${response.status} - ${response.statusText}</div>` +
    `<div style="width: 100%; text-align: center;"><img src="/images/the.shipyard.jpg" width="100%"></div>` +
    `<div style='border: 3px solid orange; border-radius: 10px; text-align: center; display: grid; grid-template-columns: auto auto auto;'>` +
    `<div style="display: flex; align-items: center; margin: auto;"><img src="/images/construction.cone.png" height="52"></div> <div><b>${mess}</b></div> <div style="display: flex; align-items: center; margin: auto;"><img src="/images/construction.cone.png" height="52"></div></div></div>`;
    return message;
};

let generateFetchErrorMessage = (contentName, error, errmess) => {
    let message;
    if (errmess) {
        let mess = JSON.parse(errmess);
        if (mess.message) {
            message = mess.message;
        }
    }
    let text = (currentLang === 'FR') ? 'Erreur de chargement...<br/>Est-ce que le serveur est en route ?<br/>Voyez votre administrateur.' :
                                        'Load error...<br/>Is the server running?<br/>See your admin.';
    let content = `<div style='margin: 10px;'><pre>Fetch Error for ${contentName}: ${(error ? JSON.stringify(error, null, 2) : ' - ') + ', ' + (message ? message : ' - ')} </pre><div style='border: 3px solid red; border-radius: 10px; text-align: center;'><b>${text}</b></div></div>`;
    return content;
};

let ZOOM_POSITIONS = [];
var map;

let closeDiv = (el) => {
    el.style.display = 'none';
}

function expandClick(origin) {
    console.log("Click happened");
    let divToShow = origin.parentNode.querySelector("div");
    if (divToShow.style.display === 'none') {
        divToShow.style.display = 'block';
        origin.innerHTML = '&#9660;';
    } else {
        divToShow.style.display = 'none';
        origin.innerHTML = '&#9658;';
    }
}

function markerOnClick(e) {
    console.log(e);

    let mess = '';
    let posIdx = -1;
    // Find position
    for (let i=0; i<ZOOM_POSITIONS.length; i++) {
        if (e.latlng.lat === ZOOM_POSITIONS[i].latlng.lat && e.latlng.lng === ZOOM_POSITIONS[i].latlng.lng) {
            console.log(`Found position index: ${i}, ${ZOOM_POSITIONS[i].txt}`);
            mess = ZOOM_POSITIONS[i].txt;
            posIdx = i;
            break;
        }
    }
	// alert(`You clicked the marker at ${e.latlng}\n${mess} `);
    let dataList = document.getElementById("pcc-bases");
    if (dataList) {
        // console.log('Ah!');
        let theDiv = dataList.children[posIdx - 1];
        let txtDiv = theDiv.querySelectorAll('div')[0];
        txtDiv.style.display = 'block';
        window.setTimeout(() => { closeDiv(txtDiv); }, 5000);
    }
}

function makeMarker(markerData) {
    L.marker([markerData.position.lat, markerData.position.lng])
     .on('click', markerOnClick) // TODO Update the page, harbor data, boat list, etc.
     .addTo(markerData.map)
     .bindPopup(`<b>${markerData.title}</b>`);
}

function decToSex(val, ns_ew) {
    let absVal = Math.abs(val);
    let intValue = Math.floor(absVal);
    let dec = absVal - intValue;
    let i = intValue;
    dec *= 60;
    let min = dec.toFixed(4);
    while (min.length < 7) {
        min = '0' + min;
    }
    let s = i + "°" + min + "'";

    if (val < 0) {
        s += (ns_ew === 'NS' ? 'S' : 'W');
    } else {
        s += (ns_ew === 'NS' ? 'N' : 'E');
    }
    return s;
}

let scrollHere = (parent) => {
    console.log('Scroll Here invoked'); // scroll-margin-top may not be appropriate here
    let href = parent.href.substring(parent.href.indexOf("#") + 1);
    // Now look for the tab-section to scroll in
    let ref = parent.parentNode;
    while (ref !== undefined && !ref.classList.contains('tab-section')) {
        console.log(`Classlist of ${ref.nodeName}: ${ref.classList}`);
        ref = ref.parentNode;
    }
    let currentDialog = document.getElementById('info-tx-dialog');
    // currentDialog.scrollTo(0, -60);
    let anchor = document.querySelectorAll(`a[name="${href}"]`);
    anchor[0].scrollIntoView();
    // currentDialog.style.marginTop = '280px';
    currentDialog.scrollIntoView();
    // document.getElementById('current-content').scrollTo(0, -60);
    // debugger;
    return event.preventDefault();
}

/**
 * Turn "N 47 40.66" into 47.677667
 * Warning: No deg sign, no min sign.
 */
function sexToDec(val, lat_lng) {
    let sign = val.substring(0, 1);
    let fullValue = val.substring(val.indexOf(' ') + 1);
    let degs = fullValue.substring(0, fullValue.indexOf(' '));
    let mins = fullValue.substring(fullValue.indexOf(' ') + 1);

    let deg = parseInt(degs);
    let min = parseFloat(mins);

    let decValue = deg + (min / 60.0);
    if (sign === 'W' || sign === 'S') {
        decValue *= -1;
    }

    return decValue;
}

function flyToZoom(idx) {
    // map.panTo(ZOOM_POSITIONS[idx]);
    // map.setView(ZOOM_POSITIONS[idx], 18);
    map.flyTo(ZOOM_POSITIONS[idx].latlng);
}

let scrollToGivenAnchor = (hashtag) => {
    const anchor = document.querySelector(`a[name='${hashtag}']`);
    if (anchor) {
        anchor.scrollIntoView();
    } else {
        console.log(`scrollToGivenAnchor: Anchor ${hashtag} not found.`);
    }
    // 2e couche
    window.scrollBy(0, -92); // 92: menu thickness
};


let initBoatClubBases = () => {

    const homeBelz     = new L.LatLng(47.677667, -3.135667);
    const labOcean     = new L.LatLng(47.591886, -3.028032);
    const etel         = new L.LatLng(47.659253, -3.208115);
    const laTrinite    = new L.LatLng(47.589018, -3.025789);
    const lesSables    = new L.LatLng(46.501142, -1.794205);
    const laRochelle   = new L.LatLng(46.146335, -1.166267);
    const concarneau   = new L.LatLng(47.870353, -3.914394);
    const kerran       = new L.LatLng(47.598399, -2.981517);
    const gavres       = new L.LatLng(47.700475, -3.351070);

    ZOOM_POSITIONS = [
        { latlng: homeBelz, txt: 'Belz Home' },
        { latlng: labOcean, txt: 'Lab Ocean, un bureau pour Passe-Coque' },
        { latlng: etel, txt: 'Etel, Manu Oviri (Commanche 32), Eh\'Tak (Shipman 28)' },
        { latlng: laTrinite, txt: 'La Trinit&eacute;, Trehudal (Nichlson 33)' },
        { latlng: lesSables, txt: 'Les Sables d\'Olonne, Jolly Jumper (First 325)' },
        { latlng: laRochelle, txt: 'La Rochelle, . . .' },
        { latlng: concarneau, txt: 'Concarneau, Nomaddict (Gin Fizz)' },
        { latlng: kerran, txt: 'ZA de Kerran, le local.' } // ,
        // { latlng: gavres, txt: 'Gâvres, Eh\'Tak (Shipman 28).' }
    ];

    map = L.map('mapid'); // .setView([currentLatitude, currentLongitude], 13);

    // let mbAttr = 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community';
    let mbUrl = // 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}';
                'https://tile.openstreetmap.org/{z}/{x}/{y}.png';

    const base_layer = L.tileLayer(mbUrl, {
        id: 'mapbox.streets',
        // attribution: mbAttr,
        opacity: 1.0
    }).addTo(map);

    // const tiles = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    //     maxZoom: 19,
    //     attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    //     opacity: 0.5
    // }).addTo(map);
    map.setView([47.598399, -2.981517], 8); // 14); // Kerran

    if (false) {
        makeMarker({
            position: homeBelz,
            map: map,
            title: 'Beg er Lann, Belz'});
    }
    makeMarker({
        position: labOcean,
        map: map,
        title: 'Lab Ocean'});
    makeMarker({
        position: etel,
        map: map,
        title: '&Eacute;tel'});
    makeMarker({
        position: laTrinite,
        map: map,
        title: 'La Trinit&eacute;'});
    makeMarker({
        position: lesSables,
        map: map,
        title: 'Les Sables d\'Olonne'});
    makeMarker({
        position: laRochelle,
        map: map,
        title: 'La Rochelle'});
    makeMarker({
        position: concarneau,
        map: map,
        title: 'Concarneau'});
    makeMarker({
        position: kerran,
        map: map,
        title: 'ZA de Kerran'});
    makeMarker({
        position: gavres,
        map: map,
        title: 'G&acirc;vres'});

    let tooltip = null;

    if (false) { // Position  (lat-lng) of the mouse
        map.addEventListener('mousemove', (event) => {
            // let lat = Math.round(event.latlng.lat * 100000) / 100000;
            // let lng = Math.round(event.latlng.lng * 100000) / 100000;
            let lat = event.latlng.lat;
            let lng = event.latlng.lng;
            while (lng > 180) {
                lng -= 360;
            }
            while (lng < -180) {
                lng += 360;
            }
            if (tooltip != null) {
                map.removeLayer(tooltip);
            }
            tooltip = L.tooltip()
                            .setLatLng(L.latLng([lat, lng]))
                            .setContent(`${decToSex(lat, "NS")}<br/>${decToSex(lng, "EW")}`)
                            .addTo(map);

        });
    }

};

let clack_pcc = (origin) => {
    let originId = '';
    if (typeof(origin) === 'string') {
        originId = origin.replace('_', '');
    } else {
        console.log(`clack_pcc: Click on ${origin.innerText}, id ${origin.id}`);
        originId = origin.id.replace('_', '');
    }
    currentContext = originId;

    let path = (document.location.pathname ? document.location.pathname + "/" : "");

	let contentName = `${path}${originId}_${currentLang}.html`;
    // Specific content rule(s)
	if (originId === "21" || originId === "22") { // Menu 2, special management, see below (ONE page only)
        contentName = `${path}2_${currentLang}.html`; // 21 & 22, same doc, different anchor (hashtag).
    } else if  (originId === "41" || originId === "42") { // TODO Remove this...
        contentName = `${path}4_${currentLang}.html`;
    } else if  (originId === "43") {                      // TODO Remove this...
        contentName = `${path}6_${currentLang}.html`;  // Member Space
    } else if  (originId === "51" || originId === "52") {  // TODO Remove this...
        contentName = `${path}5_${currentLang}.html`;
    // } else if  (originId === "6") {
    //   contentName = `${path}6_${currentLang}.html`;   // Member Space
    }

    let contentPlaceHolder = document.getElementById("current-content");

	fetch(contentName)
            .then(response => {  // Warning... the NOT_FOUND error lands here, apparently.
                console.log(`Data Response: ${response.status} - ${response.statusText}`);
				if (response.status !== 200) { // There is a problem...
					contentPlaceHolder.innerHTML = generateFetchMessage(contentName, response); // `Fetching ${contentName}...<br/> Data Response: ${response.status} - ${response.statusText}<br/><b>En d&eacute;veloppement...<br/>Disponible prochainement.</b>`;
				} else {
					response.text().then(doc => {
						console.log(`Code data loaded, length: ${doc.length}.`);
                        contentPlaceHolder.innerHTML = doc;
						// Some specific cases here
                        let hashtag = null;
                        let overflow = null;

                        if (originId === "21" || originId === "22") {
                            hashtag = (originId === "21") ? '01' : ((originId === "22") ? '02' : 'XX');
                            overflow = document.getElementById('pcc-2');
                        } else if (originId === "41" || originId === "42") {
                            hashtag = (originId === "41") ? '01' : ((originId === "42") ? '02' : 'XX');
                            overflow = document.getElementById('pcc-4');
                        } else if (originId === "51" || originId === "52") {
                            hashtag = (originId === "51") ? '01' : ((originId === "52") ? '02' : 'XX');
                            overflow = document.getElementById('pcc-5');
                        }

						if (hashtag !== null && overflow !== null) { // Menu 2, One page only, with anchors.
                            // let nbTry = 0;
                            let scrollToAnchor = () => {
                                // const overflow = document.getElementById('pcc-2');
                                // let hashtag = (originId === "21") ? '01' : ((originId === "22") ? '02' : 'XX');
                                const anchor = document.querySelector(`a[name='${hashtag}']`);
                                anchor.scrollIntoView();

                                /*
                                const rectOverflow = overflow.getBoundingClientRect();
                                const rectAnchor = anchor.getBoundingClientRect();

                                let scroll_top = rectAnchor.top - rectOverflow.top;
                                console.log(`rectAnchor.top: ${rectAnchor.top}, rectOverflow.top: ${rectOverflow.top} => ${scroll_top}`);
                                // Set the scroll position of the overflow container
                                overflow.scrollTop = scroll_top; // .toFixed(0);  // If remains to zero, check div's height
                                console.log(`>>> Origin: ${originId} #${hashtag}: scrolltop: ${overflow.scrollTop} vs ${scroll_top}`);
                                */
                                // 2e couche
                                if (originId === "21" || originId === "41" || originId === "51") {
                                    window.scrollTo(0, 0); // Scroll on top, if invoked from a button at the bottom of the page
                                }
                                window.scrollBy(0, -92); // 92: menu thickness
                            };
                            window.setTimeout(scrollToAnchor, 200); // Timeout to wait for the load of the fetch...
                            if (false) {
                                // 2e couche
                                if (originId === "21" || originId === "41" || originId === "51") {
                                    window.scrollTo(0, 0); // Scroll on top, if invoked from a button at the bottom of the page
                                }
                                debugger;
                                // window.scrollTo();
                            }
                        } else {
                            if (true) {
                                contentPlaceHolder.innerHTML = doc;
                                if (originId === "32") {
                                    window.setTimeout(() => {
                                        fillOutFleet(CLUB, "share-container", false, '../'); // Populate PCC boat list
                                    }, 500);
                                } else if (originId === "31") {
                                    // Initialize Leaflet
                                    window.setTimeout(() => {
                                        initBoatClubBases();
                                    }, 500);
                                }
                                window.scrollTo(0, 0); // Scroll on top, if invoked from a button at the bottom of the page
                            }
                        }
					});
				}
            },
            (error, errmess) => {
                console.log("Ooch");
                let message;
                if (errmess) {
                    let mess = JSON.parse(errmess);
                    if (mess.message) {
                        message = mess.message;
                    }
                }
                console.debug("Failed to get code data..." + (error ? JSON.stringify(error, null, 2) : ' - ') + ', ' + (message ? message : ' - '));
				// Plus tard...
				contentPlaceHolder.innerHTML = generateFetchErrorMessage(contentName, error, errmess);
            });
}

let to_club_and_clack = (origin) => {
    let newUrl = `${window.location.origin}/boat-club/?clack=${origin}&lang=${currentLang}`;
    // console.log(`Opening PCC target for ${newUrl}`);
    // https://www.w3schools.com/jsref/met_win_open.asp
    let ret = window.open(newUrl, "PCC"); // The target prm seems to have a problem, sometimes. Put NO target in the origin href !...
    if (!ret) {
        console.log(">>> window.open failed ??");
    }
}

let updateMenuPCC = () => { // Multilang aspect.
    document.querySelectorAll("#home-label").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "Accueil" : "Home"));
	document.querySelectorAll("#_2").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "Le Club&nbsp;" : "The Club&nbsp;"));
	document.querySelectorAll("#_21").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "Fonctionnement" : "How it works"));
	document.querySelectorAll("#_22").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "La Charte" : "The Charter"));

	document.querySelectorAll("#_3").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "Nos bateaux" : "Our boats"));
	document.querySelectorAll("#_31").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "Les ports" : "The harbors"));
	document.querySelectorAll("#_32").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "La flotte" : "The fleet"));

	document.querySelectorAll("#_4").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "Adh&eacute;rer&nbsp;" : "Enroll&nbsp;"));
	// document.querySelectorAll("#_41").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "Pour quoi faire" : "What for"));
	// document.querySelectorAll("#_42").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "Adh&eacute;rer au boat club" : "Enrollment"));
	// document.querySelectorAll("#_43").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "Espace Membres" : "Members Space"));

	document.querySelectorAll("#_5").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "R&eacute;server&nbsp;" : "Reservations&nbsp;"));
	// document.querySelectorAll("#_51").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "Comment &ccedil;a marche" : "How it works"));
	// document.querySelectorAll("#_52").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "Faire une r&eacute;servation" : "Make a reservation"));
	// document.querySelectorAll("#_53").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "Espace Membres" : "Members Space"));

    document.querySelectorAll("#members-space").forEach(elmt => elmt.innerHTML = (currentLang === "FR" ? "Membres" : "Members"));
};

let switchLanguagePCC = () => {
    if (currentLang === "FR") { // Then switch to EN
        document.querySelectorAll("#lang-flag").forEach(flagElement => {
            flagElement.src = "../france.gif";
            flagElement.alt = "Drapeau français";
            flagElement.title = "En français";
        });
        currentLang = "EN";
    } else {
        document.querySelectorAll("#lang-flag").forEach(flagElement => {
            flagElement.src = "../us_uk_flag.png"; // "./usa.gif";
            flagElement.alt = "US Flag";
            flagElement.title = "Switch to English";
        });
        currentLang = "FR";
    }
	// Le reste...
	updateMenuPCC();

    // Update currently displayed content
    let newId = `_${currentContext}`;
    let el = document.createElement("div");
	el.id = newId;
	clack_pcc(el);
};

const BG_IMAGES =
/*[
	"../backgrounds/la.licorne.jpeg",
	"../backgrounds/next_wave.jpg"
];*/

/*[ "jeff.01/DSCF2058.jpg",        "jeff.01/IMG_0319.jpg",        "jeff.01/IMG_6647.jpg",        "jeff.01/IMG_8100.jpg",       "jeff.01/P1010473.jpg",
  "jeff.01/DSC_0519.jpg",        "jeff.01/IMG_0336.jpg",        "jeff.01/IMG_6662.jpg",        "jeff.01/IMG_9441.jpg",
  "jeff.01/IMG_0207.jpg",        "jeff.01/IMG_0486.jpg",        "jeff.01/IMG_6663.jpg",        "jeff.01/IMG_9893.jpg",
  "jeff.01/IMG_0218.jpg",        "jeff.01/IMG_1082.jpg",        "jeff.01/IMG_8034.jpg",        "jeff.01/P1000587.jpg" ]; */

// [ "./photos.michel.01/quille.coraxy.jpg",
//   "./photos.michel.02/01.jpg",
//   "./photos.michel.02/02.jpg",
//   "./photos.michel.02/03.jpg" ];

[ "/images/houat.jpg",
  "/images/mouillage.01.jpg",
  "/images/mouillage.02.jpg",
  "/images/sunset.jpg",
  "/images/evo.png",
  "/images/slideshow.01.png",
  "/images/slideshow.02.png",
  "/images/slideshow.03.png",
  "/images/slideshow.04.png",
  "/images/slideshow.05.png",
  "/images/la.mer.jpg" ];

const BG_INTERVAL = 5000; // in ms

let current_bg_image_index = 0;
let bgAnimation;

let startBGAnimation = (cb) => {
	if (cb) {
		if (!cb.checked) {
			clearInterval(bgAnimation);
			bgAnimation = null;
		} else {
			bgAnimation = setInterval(() => {
				current_bg_image_index += 1;
				if (current_bg_image_index >= BG_IMAGES.length) {
					current_bg_image_index = 0;
				}
				document.getElementById("bg-image").src = BG_IMAGES[current_bg_image_index];
			}, BG_INTERVAL); // in ms.
		}
	} else { // Previous behavior
		if (bgAnimation !== undefined) {
		    clearInterval(bgAnimation);
		}
		// setTimeout(darkenBackground, 1);
		bgAnimation = setInterval(() => {
			current_bg_image_index += 1;
			if (current_bg_image_index >= BG_IMAGES.length) {
				current_bg_image_index = 0;
			}
            try {
                let slideShowContainer = document.getElementById("bg-image");
                if (slideShowContainer) {
			        slideShowContainer.src = BG_IMAGES[current_bg_image_index];
                } // else, on another page...
            } catch (err) {
                console.log(`Managed error ${JSON.stringify(err)} (${err})`);
            }
		}, BG_INTERVAL); // in ms.
	}
};

// Left-right swipe gesture management on the slides
let xDown = null;
let yDown = null;

function handleTouchStart(evt) {
    xDown = evt.touches[0].clientX;
    yDown = evt.touches[0].clientY;
}

function handleTouchMove(evt) {
    if (!xDown || !yDown) {
        return;
    }
    let xUp = evt.touches[0].clientX;
    let yUp = evt.touches[0].clientY;
    let xDiff = xDown - xUp;
    let yDiff = yDown - yUp;

    // most significant
    if (Math.abs(xDiff) > Math.abs(yDiff)) { // Left-right
        if (xDiff > 0) {
        /* left swipe */
        plusSlides(1);
        } else {
        /* right swipe */
        plusSlides(-1);
        }
    } else { // Up-Down, not needed here (yet...)
        if (yDiff > 0) {
        /* up swipe */
        } else {
        /* down swipe */
        }
    }
    /* reset values */
    xDown = null;
    yDown = null;
}

// const BG_INTERVAL = 2 * 60 * 1000; // in ms.
let secondLeft = BG_INTERVAL / 1000;

let aboutDiv = undefined;

let auto = false;

function manageClick(cb) {
    auto = cb.checked;
    if (auto) {
        showSlides(slideIndex);
    }
}

let slideIndex = 1;
// showSlides(slideIndex);

function plusSlides(n) {
    showSlides(slideIndex += n);
}

function currentSlide(n) {
    showSlides(slideIndex = n);
}

function showSlides(n) {
    let slides = document.getElementsByClassName("the-slides");
    let dots = document.getElementsByClassName("dot");
    if (n > slides.length) {
        slideIndex = 1;
    }
    if (n < 1) {
        slideIndex = slides.length;
    }
    for (let i = 0; i < slides.length; i++) { // Hide them all
        //  slides[i].style.display = "none";
        slides[i].classList.remove("visible-slide");
    }

    if (!auto) {
        for (let i = 0; i < dots.length; i++) {
            dots[i].className = dots[i].className.replace(" active", "");
        }
    //    slides[slideIndex - 1].style.display = "block";
        slides[slideIndex - 1].classList.add("visible-slide"); // Show active one

        dots[slideIndex - 1].className += " active";
    } else { // Auto

        slideIndex++;
        if (slideIndex > slides.length) {
            slideIndex = 1
        }
        for (let i = 0; i < dots.length; i++) {
            dots[i].className = dots[i].className.replace(" active", "");
        }
    //    slides[slideIndex - 1].style.display = "block";
        slides[slideIndex - 1].classList.add("visible-slide");

        dots[slideIndex - 1].className += " active";
        let TEN_SEC = 10000;
        setTimeout(showSlides, TEN_SEC);
    }
}

let sendEmail = (first, last) => {
    console.log(`Sending email from ${first}, ${last}`);
    let sender = `Message from ${first} ${last}\n`;
    window.open(`mailto:contact@passe-coque.com?subject=${encodeURI(sender)}`);
};

let getQueryParameterByName = (name, url) => {
    if (!url) {
        url = window.location.href;
    }
    name = name.replace(/[\[\]]/g, "\\$&");
    let regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) {
        return null;
    }
    if (!results[2]) {
        return '';
    }
    return decodeURIComponent(results[2].replace(/\+/g, " "));
};

let qrcode;

let makeCode = (url, location) => {
	if (url === undefined) {
		url = document.location.href;
	}
    console.log(`QR Code for ${url}`);
	let docLoc = url;
	let toDisplay = docLoc; // .substring(0, docLoc.lastIndexOf('/')) + "/" + url; // document.location + url;

  	qrcode.makeCode(toDisplay);
    // qrcode.style.display = 'block';
    if (location) {
        // let content = document.getElementById("qrcode").innerHTML;
        document.getElementById(location).appendChild(document.getElementById("qrcode"));
        document.getElementById(location).firstChild.style.display = 'block';
    } else {
        document.getElementById("qrcode").style.display = 'block'; // Show it ! (Hidden otherwise)
    }
};

const DIALOG_OPTION = true;

// Mouse behavior, on some specific pages (or snippets)
let clickOnTxPix = (origin, title = '') => {
    console.log(`clickOnTxPix: Click on ${origin.id}`);

    let dynamicContentContainer = DIALOG_OPTION ? document.getElementById("dialog-tx-content") : document.getElementById("info-tx");
    let dialogTitle = document.querySelectorAll('.dialog-title'); // dialog-title
    if (dialogTitle) {
        dialogTitle[dialogTitle.length - 1].innerHTML = title; // Can be several dialogs... take the last.
        dialogTitle[dialogTitle.length - 1].title = origin.id; // Bonus !
    }

    let contentName = `${origin.id}_${currentLang}.html`; // Like 'tx-01_FR.html'
    fetch(contentName)
        .then(response => {  // Warning... the NOT_FOUND error lands here, apparently.
            console.log(`Data Response: ${response.status} - ${response.statusText}`);
            if (response.status !== 200) { // There is a problem...
                dynamicContentContainer.innerHTML = generateFetchMessage(contentName, response); // `Fetching ${contentName}...<br/> Data Response: ${response.status} - ${response.statusText}<br/><b>En d&eacute;veloppement...<br/>Disponible prochainement.</b>`;
            } else {
                response.text().then(doc => {
                    console.log(`${contentName} code data loaded, length: ${doc.length}.`);
                    dynamicContentContainer.innerHTML = doc;
                    if (title === '') {
                        // GET IT FROM THE DOC !!
                        try {
                            const parser = new DOMParser();
                            const content = parser.parseFromString(doc, "text/xml");
                            let innerTitle = content.querySelectorAll(".boat-card")[0].getAttribute("header");
                            if (dialogTitle) {
                                dialogTitle[dialogTitle.length - 1].innerText = innerTitle; // Can be several dialogs... take the last.
                                dialogTitle[dialogTitle.length - 1].title = origin.id; // Bonus !
                            }
                        } catch (err) {
                            console.log(`Managed error ${JSON.stringify(err)} (${err})`);
                        }
                    }
                });
            }
        },
        (error, errmess) => {
            console.log("Ooch");
            let message;
            if (errmess) {
                let mess = JSON.parse(errmess);
                if (mess.message) {
                    message = mess.message;
                }
            }
            console.debug("Failed to get code data..." + (error ? JSON.stringify(error, null, 2) : ' - ') + ', ' + (message ? message : ' - '));
            // Plus tard...
            dynamicContentContainer.innerHTML = generateFetchErrorMessage(contentName, error, errmess);
        });

    // dynamicContentContainer.innerHTML = content;
    if (DIALOG_OPTION) {
        showInfoTxDialog();
    } else {
        dynamicContentContainer.style.display = 'block';
    }
};

let mouseOnTxPix = (origin) => {
    console.log(`Mouse on ${origin.id}`);
    // origin.title = (currentLang === 'FR') ? "Vas-y, clique !" : "Click for more.";
    let tooltipHolder = origin.querySelector('span');
    if (false && tooltipHolder) {
        tooltipHolder.innerHTML = (currentLang === 'FR') ? "Vas-y,<br/>clique sur la photo !" : "Click the picture<br/>for more.";
    } else { // Just in case no <span> child is found...
        origin.title = (currentLang === 'FR') ? "Cliquer pour plus d'info" : "Click for more.";
        origin.style.cursor = 'pointer';
        // let image = origin.querySelector('img');
        // if (image) {
        //     image.style.opacity = 1.0;
        // }
    }
};

let mouseOnRftPix = (origin) => {
};

let clickOnBoatPix = (origin, name = '', pathPrefix = '') => {
    console.log(`clickOnBoatPix: Click on ${origin.id}`);

    if (name.trim().length === 0) {
        try {
            THE_FLEET.forEach(element => { // Use THE_FLEET, getTheBoats might not have been invoked yet...
                if (element.id === origin.id) {
                    name = element.name;
                }
            });
        } catch (err) {
            console.log(err);
        }
    }

    // TODO Set the content
    let dynamicContentContainer = DIALOG_OPTION ? document.getElementById("dialog-tx-content") : document.getElementById("info-tx");
    let dialogTitle = document.querySelectorAll('.dialog-title'); // dialog-title
    if (dialogTitle && dialogTitle.length > 0) {
        dialogTitle[dialogTitle.length - 1].innerHTML = name; // Can be several dialogs... take the last.
    }

    let contentName = `${pathPrefix}${origin.id}_${currentLang}.html`; // Like 'tx-01_FR.html'
    console.log(`onclick, loading ${contentName}`);
    fetch(contentName)
        .then(response => {  // Warning... the NOT_FOUND error lands here, apparently.
            console.log(`Data Response: ${response.status} - ${response.statusText}`);
            if (response.status !== 200) { // There is a problem...
                dynamicContentContainer.innerHTML = generateFetchMessage(contentName, response); // `Fetching ${contentName}...<br/> Data Response: ${response.status} - ${response.statusText}<br/><b>En d&eacute;veloppement...<br/>Disponible prochainement.</b>`;
            } else {
                response.text().then(doc => {
                    console.log(`${contentName} code data loaded, length: ${doc.length}.`);
                    dynamicContentContainer.innerHTML = doc;
                });
            }
        },
        (error, errmess) => {
            console.log("Ooch");
            let message;
            if (errmess) {
                let mess = JSON.parse(errmess);
                if (mess.message) {
                    message = mess.message;
                }
            }
            console.debug("Failed to get code data..." + (error ? JSON.stringify(error, null, 2) : ' - ') + ', ' + (message ? message : ' - '));
            // Plus tard...
            dynamicContentContainer.innerHTML = generateFetchErrorMessage(contentName, error, errmess); // `<b>${contentName} ${currentLang === 'FR' ? ' introuvable...<br/>Bient&ocirc;t dispo !' : ' not found...<br/>Avai;able soon!'}</b>`;
        });

    // dynamicContentContainer.innerHTML = content;
    if (DIALOG_OPTION) {
        showInfoTxDialog();
    } else {
        dynamicContentContainer.style.display = 'block';
    }
};

let displaySpecific = (docName) => {
    console.log(`displaySpecific`);
    // Set the content
    let dynamicContentContainer = DIALOG_OPTION ? document.getElementById("dialog-tx-content") : document.getElementById("info-tx");
    let dialogTitle = document.querySelectorAll('.dialog-title'); // dialog-title
    if (dialogTitle) {
        dialogTitle[dialogTitle.length - 1].innerText = ''; // Can be several dialogs... take the last.
    }
    let contentName = `${docName}_${currentLang}.html`; // Like 'tagada_FR.html'
    console.log(`onclick, loading ${contentName}`);
    fetch(contentName)
        .then(response => {  // Warning... the NOT_FOUND error lands here, apparently.
            console.log(`Data Response: ${response.status} - ${response.statusText}`);
            if (response.status !== 200) { // There is a problem...
                dynamicContentContainer.innerHTML = generateFetchMessage(contentName, response); // `Fetching ${contentName}...<br/> Data Response: ${response.status} - ${response.statusText}<br/><b>En d&eacute;veloppement...<br/>Disponible prochainement.</b>`;
            } else {
                response.text().then(doc => {
                    console.log(`${contentName} code data loaded, length: ${doc.length}.`);
                    dynamicContentContainer.innerHTML = doc;
                });
            }
        },
        (error, errmess) => {
            console.log("Ooch");
            let message;
            if (errmess) {
                let mess = JSON.parse(errmess);
                if (mess.message) {
                    message = mess.message;
                }
            }
            console.debug("Failed to get code data..." + (error ? JSON.stringify(error, null, 2) : ' - ') + ', ' + (message ? message : ' - '));
            // Plus tard...
            dynamicContentContainer.innerHTML = generateFetchErrorMessage(contentName, error, errmess); // `<b>${contentName} ${currentLang === 'FR' ? ' introuvable...<br/>Bient&ocirc;t dispo !' : ' not found...<br/>Avai;able soon!'}</b>`;
        });

    // dynamicContentContainer.innerHTML = content;
    if (DIALOG_OPTION) {
        showInfoTxDialog();
    } else {
        dynamicContentContainer.style.display = 'block';
    }
};

let showInfoTxDialog = () => {
    let infoTxDialog = document.getElementById("info-tx-dialog");
    window.scrollTo(0, 0); // Scroll on top, for Safari and others...
    if (infoTxDialog.show !== undefined) {
        infoTxDialog.style.display = 'inline'; // Safari...
        infoTxDialog.show();
    } else {
      // alert(NO_DIALOG_MESSAGE);
      infoTxDialog.style.display = 'inline';
    }
};

let closeInfoTxDialog = () => {
    let infoTxDialog = document.getElementById("info-tx-dialog");
    if (infoTxDialog.close !== undefined) {
        infoTxDialog.style.display = 'none'; // Safari
        infoTxDialog.close();
    } else {
      // alert(NO_DIALOG_MESSAGE);
      infoTxDialog.style.display = 'none';
    }
};

let aboutSomeone = (who) => {
    console.log(`About ${who.id}`);
    let aboutDialog = document.getElementById("about-dialog");
    let dialogContent = document.getElementById("dialog-content");

    let contentName = `about_${who.id}_${currentLang}.html`;

    fetch(contentName)
        .then(response => {  // Warning... the NOT_FOUND error lands here, apparently.
            console.log(`Data Response: ${response.status} - ${response.statusText}`);
            if (response.status !== 200) { // There is a problem...
                dialogContent.innerHTML = generateFetchMessage(contentName, response); // `Fetching ${contentName}...<br/> Data Response: ${response.status} - ${response.statusText}<br/><b>En d&eacute;veloppement...<br/>Disponible prochainement.</b>`;
            } else {
                response.text().then(doc => {
                    console.log(`${contentName} code data loaded, length: ${doc.length}.`);
                    dialogContent.innerHTML = doc;
                });
            }
        },
        (error, errmess) => {
            console.log("Ooch");
            let message;
            if (errmess) {
                let mess = JSON.parse(errmess);
                if (mess.message) {
                    message = mess.message;
                }
            }
            console.debug("Failed to get code data..." + (error ? JSON.stringify(error, null, 2) : ' - ') + ', ' + (message ? message : ' - '));
            // Plus tard...
            dialogContent.innerHTML = generateFetchErrorMessage(contentName, error, errmess); // `<b>${contentName} ${currentLang === 'FR' ? ' introuvable...<br/>Bient&ocirc;t dispo !' : ' not found...<br/>Avai;able soon!'}</b>`;
        });

    let topOfCard = window.scrollY; // pageYOffset; // who.getBoundingClientRect().top;

    // window.scrollTo(0, 0); // Scroll on top, for Safari and others...
    let marginTop = `${topOfCard.toFixed(0)}px`;
    console.log(`Dialog top margin: ${marginTop}`);
    aboutDialog.style.marginTop = marginTop;

    if (aboutDialog.show !== undefined) {
        aboutDialog.style.display = 'block'; // Safari...
        aboutDialog.show();
    } else {
      // alert(NO_DIALOG_MESSAGE);
      aboutDialog.style.display = 'block'; // 'inline';
    }
};

let aboutPartner = (from) => {
    // console.log(`About Partner`);
    let partnerURL = from.getAttribute('url');  // Non standard !
    if (partnerURL) {
        window.open(partnerURL);
    }
};

let onMouseOverImage = (origin) => {
    console.log(`onMouseOverImage : ${origin}`);
};

let onImageClick = (origin, target) => {
    console.log(`onImageClick : ${JSON.stringify(origin)}`);
    if (target === undefined || target === null) {
        window.open(origin.src);
    } else {
        window.open(origin.src, target);
    }
};

const NONE = "NONE";         // 1;
const CLUB = "CLUB";         // 2;
const EX_BOAT = "EX_BOAT";   // 3;
const TO_GRAB = "TO_GRAB";   // 4;
const FOR_SALE = "FOR_SALE"; // 5;
const PARTNERS = "PARTNERS"; // 6;

let THE_BOATS = null;
// This is a backup. Used if the json fetch fails...
const THE_FLEET = [
    {
        name: "Tanikel",
        id: "tanikel",
        pix: "/images/boats/tanikel/01.jpg",
        type: "Delph 32",
        category: NONE,
        base: "La Ciotat"
    },{
        name: "Shadok",
        id: "shadok",
        pix: "/shadok/shadok.01.png",
        type: "Via 42",
        category: NONE,
        base: "Port St Louis du Rh&ocirc;ne"
    },{
        name: "Jericho",
        id: "jericho",
        pix: "/images/boats/jericho/jericho-3.jpg",
        type: "Cotre bermudien",
        category: NONE,
        base: "St Philibert"
    },{
        name: "La Maoa",
        id: "la-maoa",
        pix: "/images/boats/la.maoa/la.plaque.jpeg",
        type: "Plate",
        category: NONE,
        base: "St Philibert"
    },{
        name: "Nuage",
        id: "nuage",
        pix: "/images/boats/nuage/Soling.02.jpeg",
        type: "Soling",
        category: NONE,
        base: "-"
    },{
        name: "Bay Watch",
        id: "coquina",
        pix: "/images/godille/coquina.02.jpeg",
        type: "Coquina, Canot voile-aviron",
        category: NONE,
        base: "-"
    },{
        name: "Penny Lane",
        id: "penny-lane",
        pix: "/images/boats/penny.lane/penny.lane.12.jpg",
        type: "Arp&egrave;ge",
        category: FOR_SALE,
        base: "-"
    }, {
        name: "Lady of Solent",
        id: "los",
        pix: "/images/boats/lady.of.solent/los.10.png",
        type: "Contessa 35",
        category: NONE,
        base: "-"
    }, {
        name: "Araben",
        id: "araben",
        pix: "/images/boats/voilieraraben/Araben 1.jpg",
        type: "Plan Mauric",
        category: PARTNERS,
        base: "Arzal"
    }, {
        name: "Eh'Tak",
        id: "eh-tak",
        pix: "/images/boats/eh-tak.jpg",
        type: "Shipman 28",
        category: CLUB,
        base: "&Eacute;tel"
    }, {
        name: "Le Brio",
        id: "le-brio",
        pix: "/images/boats/dummy.boat.jpg",
        type: "Brio",
        category: EX_BOAT,
        base: "Saint Philibert"
    }, {
        name: "Pordin-Nancq",
        id: "pordin-nancq",
        pix: "/images/boats/pordin.jpg",
        type: "Carter 37",
        category: NONE,
        base: "Locmiqu&eacute;lic"
    },
    {
        name: "Zephir",
        id: "zephir",
        pix: "/images/boats/zephir/zephir.png",
        type: "Birvidic 700",
        category: NONE,
        base: "Kernevel"
    },
    {
        name: "Coraxy",
        id: "coraxy",
        pix: "/images/coraxy/coraxy.png",
        type: "Cognac",
        category: NONE,
        base: "Saint&#8209;Philibert"
    },
    {
        name: "Manu Oviri",
        id: "manu-oviri",
        pix: "/images/boats/manu-aviri.jpg",
        type: "Comanche 32",
        category: CLUB,
        base: "&Eacute;tel"
    },
    // {
    //     name: "Pen Duick",
    //     id: "pen-duick",
    //     pix: "/images/boats/pen.duick.jpg",
    //     type: "W. Fife",
    //     category: NONE,
    //     base: "Lorient"
    // },
    {
        name: "Wanita Too",
        id: "wanita",
        pix: "/images/boats/wanita.too.sq.png",
        type: "First Class 12",
        category: EX_BOAT,
        base: "Saint-Malo"
    },
    {
        name: "Atlantide",
        id: "atlantide",
        pix: "/images/boats/atlantide.sq.png",
        type: "Gib'Sea&nbsp;33",
        category: EX_BOAT,
        base: "--"
    },
    {
        name: "Iapyx",
        id: "iapyx",
        pix: "/images/boats/iapyx.sq.png",
        type: "Offshore&nbsp;35",
        category: EX_BOAT,
        base: "--"
    },
    {
        name: "Ar Mor Van",
        id: "ar-mor-van",
        pix: "/images/boats/kelt620.jpeg",
        type: "Ketl 620",
        category: NONE,
        base: "&Eacute;tel"
    },
    {
        name: "Twist Again",
        id: "twist-again",
        pix: "/images/boats/twist.again.sq.png",
        type: "JOD 35",
        category: EX_BOAT,
        base: "Saint&#8209;Philibert"
    },
    {
        name: "Ia Orana",
        id: "ia-orana",
        pix: "/images/boats/ia.orana.sq.png",
        type: "Milord",
        category: EX_BOAT,
        base: "--"
    },
    {
        name: "Melkart",
        id: "melkart",
        pix: "/images/boats/melkart/melkart.00.jpg",
        type: "Evasion 32",
        category: NONE,
        base: "&Eacute;tel"
    },
    {
        name: "Babou",
        id: "babou",
        pix: "/images/boats/babou.sq.png",
        type: "Flying Phantom",
        category: EX_BOAT,
        base: "Saint&#8209;Philibert"
    },
    {
        name: "Mirella",
        id: "mirella",
        pix: "/images/boats/mirella.png",
        type: "Maica 12,50",
        category: NONE,
        base: "Saint&nbsp;Brieuc"
    },
    {
        name: "Tri Yann",
        id: "tri-yann",
        pix: "/images/boats/tri.yann.png",
        type: "Trimaran Allegro",
        category: CLUB,
        base: "Saint&#8209;Philibert"
    },
    {
        name: "Rozen an Avel",
        id: "rozen-an-avel",
        pix: "/images/boats/rozen.an.avel.jpeg",
        type: "Arp&egrave;ge",
        category: NONE,
        base: "Saint&#8209;Philibert"
    },
    {
        name: "Avel Mad",
        id: "avel-mad",
        pix: "/images/boats/avel.mad.sq.png",
        type: "Mousquetaire",
        category: EX_BOAT,
        base: "Le&nbsp;Bono"
    },
    {
        name: "F&eacute;licie",
        id: "felicie",
        pix: "/images/boats/felicie.sq.png",
        type: "One off Presles",
        category: EX_BOAT,
        base: "Dakar"
    },
    {
        name: "La R&ecirc;veuse",
        id: "la.reveuse",
        pix: "/images/boats/la.reveuse.sq.png",
        type: "Damien 40",
        category: NONE,
        base: "Arzal"
    },
    {
        name: "Tokad 2",
        id: "tokad-2",
        pix: "/images/boats/tokad.2.sq.png",
        type: "Neptune 99",
        category: CLUB,
        base: "Le&nbsp;Crouesty"
    },
    {
        name: "Taapuna",
        id: "taapuna",
        pix: "/images/boats/taapuna.png",
        type: "Edel 660",
        category: CLUB,
        base: "Rivi&egrave;re de Saint&#8209;Philibert"
    },
    {
        name: "L'heure bleue",
        id: "heure-bleue",
        pix: "/images/boats/lheure.bleue.jpeg",
        type: "Arp&egrave;ge",
        category: NONE,
        base: "Golfe&nbsp;du&nbsp;Morbihan"
    },
    {
        name: "Jolly Jumper",
        id: "jolly-jumper",
        pix: "/images/boats/jolly.jumper.01.jpg",
        type: "First 325",
        category: NONE,
        base: "Les&nbsp;Sables&nbsp;d'Olonne"
    },
    {
        name: "Passpartout",
        id: "passpartout",
        pix: "/images/boats/passpartout.sq.png",
        type: "One off",
        category: NONE,
        base: "Lorient"
    },
    {
        name: "Melvan",
        id: "melvan",
        pix: "/images/boats/melvan.sq.png",
        type: "Karat&eacute;&nbsp;33",
        category: NONE,
        base: "Toulon"
    },
    {
        name: "Saigane",
        id: "saigane",
        pix: "/images/boats/saigane/saigane.jpg",
        type: "Dufour 2800",
        category: CLUB,
        base: "Port&nbsp;Blanc"
    },
    {
        name: "Anao",
        id: "anao",
        pix: "/images/boats/anao.jpeg",
        type: "Folie Douce",
        category: EX_BOAT,
        base: "&Eacute;tel"
    },
    {
        name: "Trehudal",
        id: "trehudal",
        pix: "/images/boats/trehudal.png",
        type: "Nicholson 33",
        category: CLUB,
        base: "La&nbsp;Trinit&eacute;"
    },
    {
        name: "Jules Verne",
        id: "jules-verne",
        pix: "/images/boats/jules.verne.sq.png",
        type: "Sir 520",
        category: NONE,
        base: "Locmariaquer"
    },
    {
        name: "Remora",
        id: "remora",
        pix: "/images/boats/remora.sq.png",
        type: "Arcachonnais",
        category: TO_GRAB,
        base: "Saint&#8209;Philibert"
    },
    {
        name: "Stiren ar Mor",
        id: "stiren",
        pix: "/images/boats/stiren.er.mor.png",
        type: "Ghibli",
        category: NONE,
        base: "La&nbsp;Trinit&eacute;"
    },
    {
        name: "Coevic 2",
        id: "coevic-2",
        pix: "/images/boats/coevic-2.png",
        type: "Mirage 28",
        category: EX_BOAT,
        base: "Lorient"
    },
    {
        name: "Ma Enez",
        id: "ma-enez",
        pix: "/images/boats/ma.enez.png",
        type: "Symphonie",
        category: NONE,
        base: "La&nbsp;Trinit&eacute;"
    },
    {
        name: "Saudade",
        id: "saudade",
        pix: "/images/boats/saudade.png",
        type: "Super Arlequin",
        category: NONE,
        base: "Le&nbsp;Bono"
    },
    {
        name: "Imagine",
        id: "imagine",
        pix: "/images/boats/selection.png",
        type: "Selection&nbsp;37",
        category: EX_BOAT,
        base: "Ouistreham"
    },
    {
        name: "Gwenillig",
        id: "gwenillig",
        pix: "/images/boats/gwenillig.png",
        type: "Eygthene 24",
        category: CLUB,
        base: "--"
    },
    {
        name: "Lohengrin",
        id: "lohengrin",
        pix: "/images/boats/lohengrin/lohengrin.png",
        type: "Ketch en Acier",
        category: EX_BOAT,
        base: "Arzal"
    },
    {
        name: "Nomaddict",
        id: "nomadict",
        pix: "/images/boats/nomadict/01.jpg",
        type: "Gin Fizz",
        category: TO_GRAB,
        base: "Concarneau"
    },
    {
        name: "Velona",
        id: "velona",
        pix: "/images/boats/velona/velona.00.jpg",
        type: "Classic Old Gaffer",
        category: TO_GRAB,
        base: "Hennebont"
    },
    {
        name: "B&eacute;mol III",
        id: "bemol",
        pix: "/images/boats/sun-rise-35-sous-spi.jpg",
        type: "Sun Rise 35",
        category: CLUB,
        base: "Saint-Philibert"
    },
    {
        name: "Shazzan",
        id: "shazzan",
        pix: "/images/boats/shazzan/01.jpeg",
        type: "Ovni 435",
        category: PARTNERS,
        base: "Patagonie, Puerto Williams"
    }
];

const THE_TEAM = [
    {
        id: "pj",
        boss: true,
        image: "/images/the.team/pj.png",
        label: {
            fr: "Pierre-Jean<br/>Pr&eacute;sident",
            en: "Pierre-Jean<br/>CEO"
        }
    }, {
        id: "regis",
        boss: false,
        image: "/images/the.team/regis.2.jpg",
        label: {
            fr: "R&eacute;gis<br/>Tr&eacute;sorier / p&eacute;dagogie, relation avec les lyc&eacute;es et les GRETA",
            en: "R&eacute;gis<br/>CFO / pedagogy, relationship with high schools and GRETA"
        }
    }, {
        id: "catherine",
        boss: false,
        image: "/images/catherine.png",
        label: {
            fr: "Catherine<br/>Secr&eacute;taire",
            en: "Catherine<br/>Secretary"
        }
    }, {
        id: "olivier",
        boss: false,
        image: "/images/olivier.png",
        label: {
            fr: "Olivier<br/>Web / High &amp; Low-Tech",
            en: "Olivier<br/>Web / High &amp; Low-Tech"
        }
    }, /* {
        id: "jeff",
        boss: false,
        image: "/images/the.team/jeff.png",
        label: {
            fr: "Jeff<br/>D&eacute;veloppement de l'Eco-Village",
            en: "Jeff<br/>Eco-Village Development"
        }
    }, */ {
        id: "alain",
        boss: false,
        image: "/images/the.team/alain.2.jpg",
        label: {
            fr: "Alain<br/>Directeur technique",
            en: "Alain<br/>CTO"
        }
    }, {
        id: "michel",
        boss: false,
        image: "/images/the.team/michel.jpeg",
        label: {
            fr: "Michel<br/>Photos",
            en: "Michel<br/>Photography"
        }
    }, {
        id: "anne",
        boss: false,
        image: "/images/anne.png",
        label: {
            fr: "Anne<br/>Communication",
            en: "Anne<br/>Communication"
        }
    }, {
        id: "stephane",
        boss: false,
        image: "/images/stephane.jpeg",
        label: {
            fr: "St&eacute;phane<br/>Monde Sportif, Course au large : FFV",
            en: "St&eacute;phane<br/>Sports World, Offshore racing: FFV"
        }
    }, {
        id: "bernard",
        boss: false,
        image: "/images/the.team/bernard.jpeg",
        label: {
            fr: "Bernard<br/>Expert Grand Large, et r&eacute;f&eacute;rent de \"La Cardinale\"",
            en: "Bernard<br/>High seas expert, and referent of \"La Cardinale\""
        }
    }, {
        id: "gng",
        boss: false,
        image: "/images/the.team/gag.jpg",
        label: {
            fr: "Gabrielle et Guy<br/>D&eacute;veloppement du Boat Club",
            en: "Gabrielle et Guy<br/>Boat Club development"
        }
    }
];

const INFO_SECTION = [{
        section: "agenda",
        content: [{
            date: "Jan-2025",
            title: "Agenda 2025",
            content: "/actu/agenda2025.html"
        }/* , {
            date: "Jan-2024",
            title: "Agenda 2024",
            content: "/actu/agenda2024.html"
        }*/ ]
    },
    {
        section: "2025",
        content: [
            {
                date: "2025",
                title: "2025",
                content: "/actu/2025/news.01.html"
            }
        ]
    },
    {
        section: "2024",
        content: [{
            date: "Dec-2024",
            title: "D&eacute;cembre 2024",
            content: "/actu/2024/late.2024.html"
        },{
            date: "Oct-2024",
            title: "Octobre 2024",
            content: "/actu/2024/vendee.globe.html"
        },{
            date: "Oct-2024",
            title: "Octobre 2024",
            content: "/actu/2024/la.reveuse.02.html"
        },{
            date: "Jul-2024",
            title: "Juillet 2024",
            content: "/actu/2024/FIM.html"
        },{
            date: "Juin-2024",
            title: "28-30 Juin 2024",
            content: "/actu/2024/RubisCup2024.html"
        },{
            date: "Mai-2024",
            title: "30 Mai 2024",
            content: "/actu/2024/peps.auray.html"
        },{
            date: "Mai-2024",
            title: "23 Mai 2024",
            content: "/actu/2024/cress.lorient.html"
        },{
            date: "Mai-2024",
            title: "13 Mai 2024",
            content: "/actu/2024/armada.html"
        },{
            date: "Avr-2024",
            title: "18 Avril 2024",
            content: "/actu/2024/cm.p.benoiton.html"
        },{
            date: "Avr-2024",
            title: "13 Avril 2024",
            content: "/actu/2024/la.reveuse.01.html"
        },{
            date: "Avr-2024",
            title: "9 Avril 2024",
            content: "/actu/2024/france-bleue.html"
        },{
            date: "Avr-2024",
            title: "2 Avril 2024",
            content: "/actu/2024/radio.balises.html"
        },{
            date: "Mar-2024",
            title: "March 2024",
            content: "/actu/2024/ocean.lab.html"
        }, {
            date: "Jan-2024",
            title: "Early 2024",
            content: "/actu/2024/bpgo.html"
        }, {
            date: "Jan-2024",
            title: "Early 2024",
            content: "/actu/2024/new.site.html"
        }]
    },
    {
        section: "2023",
        content: [{
                date: "Oct-2023",
                title: "Festival des aventuriers",
                content: "/actu/2023/fam.html"
            },{
                date: "Sep-2023",
                title: "Forum Asso",
                content: "/actu/2023/forum.html"
            },{
                date: "Sep-2023",
                title: "Assises de la mer",
                content: "/actu/2023/assises.html"
            },{
                date: "Aug-2023",
                title: "Carter Cup",
                content: "/actu/2023/carter.cup.html"
            },{
                date: "Chepakan",
                title: "Passe-Coque Trophy",
                content: "/actu/2023/passe-coque.trophy.html"
            }
        ]
    },
    {
        section: "2022",
        content: [
            {
                date: "2022",
                title: "2022",
                content: "/actu/2022/news.01.html"
            },
            {
                date: "2022",
                title: "2022",
                content: "/actu/2022/news.02.html"
            }
        ]
    },
    {
        section: "2021",
        content: [
            {
                date: "2021",
                title: "2021",
                content: "/actu/2021/year.html"
            }
        ]
    },
    {
        section: "2020",
        content: [
            {
                date: "2020",
                title: "2020",
                content: "/actu/2020/year.html"
            }
        ]
    },
    {
        section: "2019",
        content: [
            {
                date: "2019",
                title: "2019",
                content: "/actu/2019/year.html"
            }
        ]
    },
    {
        section: "news",
        content: [
            {
              date: "",
              title: "All news letters",
              content: "/actu/newsletters.html"
            }
        ]
    },
    {
        section: "communications",
        content: [
            {
                date: "",
                title: "All Communications",
                content: "/actu/communications.html"
              }
          ]
    }
];

const NEXT_EVENTS = [ // oldest to newest.
    {
        date_from: '2024-05-01',
        date_to: '2024-05-01',
        content: {
            fr: '1<sup>er</sup>Mai.',
            en: 'May 1st, 2024.'
        }
    }, {
        date_from: '2024-09-07',
        date_to: '2024-09-07',
        content: {
            fr: 'le 7 septembre 2024 : Journ&eacute;e des associations &agrave; Saint Philibert, salle du Mousker.',
            en: 'September 7, 2024: Associations day, in Saint Philibert, salle du Mousker.'
        }
    }, {
        date_from: '2024-09-24',
        date_to: '2024-09-24',
        content: {
            fr: 'le 24 septembre 2024 : <a href="https://gcft.fr/events/nautik-deiz-a-saint-brieuc/" target="_blank">Nautik Diez</a> &agrave; St Brieuc',
            en: 'September 24, 2024: <a href="https://gcft.fr/events/nautik-deiz-a-saint-brieuc/" target="_blank">Nautik Diez</a> in St Brieuc'
        }
    }, {
        date_from: '2024-09-25',
        date_to: '2024-09-26',
        content: {
            fr: '25 &amp; 26 septembre 2024 : <a href="https://www.seisme.org/forum-seisme/" target="_blank">Forum S&eacute;isme</a> &agrave; Rennes',
            en: 'september 25 &amp; 26, 2024 : <a href="https://www.seisme.org/forum-seisme/" target="_blank">Forum S&eacute;isme</a> in Rennes'
        }
    }, {
        date_from: '2024-10-01',
        date_to: '2024-10-06',
        content: {
            fr: 'du 1er au 6 octobre 2024 : Passe-Coque est au <a href="https://grand-pavois.com/en/homepage/" target="_blank">Grand Pavois</a> &agrave; La Rochelle',
            en: 'October 1 to 6, 2024: Passe-Coque will be at the <a href="https://grand-pavois.com/en/homepage/" target="_blank">Grand Pavois</a> in La Rochelle'
        }
    }, {
        date_from: '2024-10-09',
        date_to: '2024-10-13',
        content: {
            fr: 'Du 9 au 13 octobre, <a href="https://aventuriersdelamer.fr/index.php/le-programme-jour-par-jour/" target="_blank">Festival des aventuriers de la mer</a>, &agrave; Lorient.',
            en: 'October 9 to 13, 2024, <a href="https://aventuriersdelamer.fr/index.php/le-programme-jour-par-jour/" target="_blank">Festival des aventuriers de la mer</a>, Lorient.'
        }
    }, {
        date_from: '2024-10-15',
        date_to: '2024-10-20',
        content: {
            fr: 'Du 15 au 20 octobre, le Nautic, Nouvelle Version, &agrave; Paris.',
            en: 'October 15 to 20, 2024, Le Nautic, new version, in Paris.'
        }
    }, {
        date_from: '2024-11-10',
        date_to: '2024-11-10',
        content: {
            fr: '10 novembre 2024, d&eacute;part du <a href="https://www.vendeeglobe.org/" target="VG">Vend&eacute;e Globe</a>, aux Sables d\'Olonne.',
            en: 'November 10, 2024, start of the <a href="https://www.vendeeglobe.org/" target="VG">Vend&eacute;e Globe</a>, in the Sables d\'Olonne.'
        }
    }, {
        date_from: '2024-12-12',
        date_to: '2024-12-12',
        content: {
            fr: '12 d&eacute;cembre 2024.<ul><li>Le Kick-off de l&apos;association Flow, cr&eacute;&eacute;e pour que le projet HUBLOW d&apos;habitat insolite &agrave; partir de bateaux recycl&eacute;s prenne son envol après avoir &eacute;t&eacute; initi&eacute; et incub&eacute; au sein de Passe- Coque. Rendez-vous au Lab Oc&eacute;an &agrave; 18h.</li><li>Le m&ecirc;me jour au Mus&eacute;e de la Marine, r&eacute;sultats de l&apos;&eacute;lection du voilier de l&apos;ann&eacute;e avec le Yacht club de France et notre partenaire Voile Magazine.</li></ul>',
            en: 'Dec 12, 2024.<ul><li>The Kick-off of the Flow association, created so that the HUBLOW project of unusual housing from recycled boats takes off after having been initiated and incubated within Passe-Coque. See you at Lab Oc&eacute;an at 6 p.m.</li><li>The same day at the Mus&eacute;e de la Marine, results of the election of the sailboat of the year with the Yacht club de France and our partner Voiles Magazine.</li></ul>'
        }
    }, {
        date_from: '2024-12-25',
        date_to: '2024-12-25',
        content: {
            fr: '25 d&eacute;cembre, No&euml;l.',
            en: 'Dec 25, XMas.'
        }
    }, {
        date_from: '2024-12-22',
        date_to: '2025-01-05',
        content: {
            fr: 'Retrouvez Arthur Le Vaillant sur FR3 dans "Littoral", tous les dimanches.',
            en: 'See Arthur Le Vaillant, every sunday on FR3, in his program "Littoral"..'
        }
    }, {
        date_from: '2025-03-28',
        date_to: '2025-03-28',
        content: {
            fr: '28 mars 2025. Premi&egrave;re formation - en beta test - Principes de base de le navigation astronomique.',
            en: 'March 28, 2025. First training - in beta test - Celestial navigation basics.'
        }
    }, {
        date_from: '2025-04-18',
        date_to: '2025-04-21',
        content: {
            fr: 'Du 18 au 21 avril 2025, Spi Ouest-France, La Trinit&eacute;.',
            en: 'April 18 to 21, 2025, Spi Ouest-France, La Trinit&eacute;.'
        }
    }, {
        date_from: '2025-05-09',
        date_to: '2025-05-11',
        content: {
            fr: 'Du 9 au 11 mai 2025, Tour de Belle-&Icirc;le.',
            en: 'May 9 to 11, 2025, Tour de Belle-&Icirc;le.'
        }
    }, {
        date_from: '2025-05-26',
        date_to: '2025-06-01',
        content: {
            fr: 'Du 26 mai au 1er juin 2025, Semaine du Golfe.',
            en: 'May 26 to June 1st, 2025, Semaine du Golfe.'
        }
    }, {
        date_from: '2025-06-13',
        date_to: '2025-06-15',
        content: {
            fr: 'Du 13 au 15 juin 2025, 150 Milles de l\'Atlantic Yacht Club.',
            en: 'June 13 to 15, 2025, Atlantic Yacht Club\'s 150 miles.'
        }
    }, {
        date_from: '2025-06-27',
        date_to: '2025-06-30',
        content: {
            fr: 'Du 27 au 30 juin 2025, Rubi\'s Cup, &agrave; Groix.',
            en: 'June 27 to 30, 2025, Rubi\'s Cup, in Groix.'
        }
    }, {
        date_from: '2025-07-24',
        date_to: '2025-07-24',
        content: {
            fr: '24 juillet 2025, ap&eacute;ro-ponton &agrave; 17:00 &agrave; Port Deun (St Philibert) avec le Parc Naturel R&eacute;gional et la commune.',
            en: 'July 24, 2025, dock-drink at 5pm in Port-Deun (St Philibert) with the PRN and the city.'
        }
    }, {
        date_from: '2025-09-23',
        date_to: '2025-09-28',
        content: {
            fr: 'Du 23 au 28 septembre 2025, Grand Pavois de La Rochelle. "La R&ecirc;veuse" avec les Conti ou "Saudade" avec Voiles Mag, les bateaux Passe-Coque vous attendent pour cette prochaine édition du salon nautique international à flot, du 23 au 28 septembre 2025 !',
            en: 'September 23 to 28, 2025, Grand Pavois in La Rochelle. "La R&ecirc;veuse" with her crew or Saudade with Voiles Mag, the Passe-Coque fleet expects you for the next edition of the international boat-show, September 23 to 28, 2025!'
        }
    }, {
        date_from: '2025-10-11',
        date_to: '2025-10-11',
        content: {
            fr: 'Samedi 11 octobre &agrave; 10:00 du matin, AG Passe-Coque, salle du Mousker &agrave; St Philibert.',
            en: 'Saturday October 11 at 10:00 in the morning, General Assembly, salle du Mousker in St Philibert.'
        }
    }, {
        date_from: '2025-11-22',
        date_to: '2025-11-22',
        content: {
            fr: 'Samedi 22 Novembre &agrave; Ingrandes sur Loire, remise des prix de la <a href="/?lang=FR&nav-to=62&where=tombola" target="tombola">Tombola des Conties</a> !',
            en: 'Saturday November 22, in Ingrande sur Loire, <a href="/?lang=EN&nav-to=62&where=tombola" target="tombola">Tombola des Conties</a>!'
        }
    }, {
        date_from: '2025-11-26',
        date_to: '2025-11-30',
        content: {
            fr: 'Du 26 au 30 novembre 2025, <a href="https://www.parisnauticshow.com/?mtm_campaign=search-notoriete&gad_source=1&gad_campaignid=22683937914&gbraid=0AAAABAKicoUV5Jp-fPIIhwb3lgP9Mx8Xd&gclid=Cj0KCQiA5uDIBhDAARIsAOxj0CEO40yq2YiF9kOs42yBkIJzsJdXaqYDDf0OsVAPAmnLXgGt_9_FstgaAljSEALw_wcB" target="PNS">Paris Nautic Show</a>, Parc des Expositions du Bourget',
            en: 'November 26 to 30, 2025, <a href="https://www.parisnauticshow.com/?mtm_campaign=search-notoriete&gad_source=1&gad_campaignid=22683937914&gbraid=0AAAABAKicoUV5Jp-fPIIhwb3lgP9Mx8Xd&gclid=Cj0KCQiA5uDIBhDAARIsAOxj0CEO40yq2YiF9kOs42yBkIJzsJdXaqYDDf0OsVAPAmnLXgGt_9_FstgaAljSEALw_wcB" target="PNS">Paris Nautic Show</a>, at the Parc des Expositions du Bourget'
        }
    }, {
        date_from: '2025-11-28',
        date_to: '2025-11-28',
        content: {
            fr: '28 novembre, remise du Prix du Voilier de l\'Ann&eacute;e 2025, lors du Paris Nautic Show.',
            en: 'November 28, award ceremony for the 2025 Sailboat of the Year, during the Paris Nautic Show.'
        }
    }, {
        date_from: '2026-01-15',
        date_to: '2026-01-15',
        content: {
            fr: 'Jeudi 15 janvier - Les Herbiers (85) - Projection du film "<a href="https://www.youtube.com/watch?v=8mFzpbdIW80" target="YT">Anita Conti, l\'appel du Large</a>" de Fr&eacute;d&eacute;ric Brunquell, en pr&eacute;sence de 2 &eacute;quipi&egrave;res et de Laurent Girault Conti.',
            en: 'Thursday January 15th - Les Herbiers (85) - Screening of the movie "<a href="https://www.youtube.com/watch?v=8mFzpbdIW80" target="YT">Anita Conti, l\'appel du Large</a>" by Fr&eacute;d&eacute;ric Brunquell, in presence of two crew members and Laurent Girault Conti.'
        }
    }, {
        date_from: '2026-02-20',
        date_to: '2026-02-20',
        content: {
            fr: 'Vendredi 20 f&eacute;vrier - Beaupr&eacute;au en Mauges (49)  - Projection du film "<a href="https://www.youtube.com/watch?v=8mFzpbdIW80" target="YT">Anita Conti, l\'appel du Large</a>" de Fr&eacute;d&eacute;ric Brunquell, en pr&eacute;sence de 2 &eacute;quipi&egrave;res et de Laurent Girault Conti.',
            en: 'Friday February 20th - Beaupr&eacute;au en Mauges (49)  - Screening of the movie "<a href="https://www.youtube.com/watch?v=8mFzpbdIW80" target="YT">Anita Conti, l\'appel du Large</a>" by Fr&eacute;d&eacute;ric Brunquell, in presence of two crew members and Laurent Girault Conti.'
        }
    }, {
        date_from: '2026-03-27',
        date_to: '2026-03-27',
        content: {
            fr: 'Vendredi 27 mars - Lorient (56) - Pr&eacute;sentation du projet au "<a href="https://pecheursdumonde.org/" target="FPM">festival des p&ecirc;cheurs du monde</a>" en pr&eacute;sence des &eacute;quipi&egrave;res.',
            en: 'Friday March 27th - Lorient (56) - Presentation of the project at the "<a href="https://pecheursdumonde.org/" target="FPM">festival des p&ecirc;cheurs du monde</a>" in presence of the crew members'
        }
    }, {
        date_from: '2026-03-28',
        date_to: '2026-03-29',
        content: {
            fr: 'Samedi 28 et Dimanche 29 Mars - Maison Glaz &agrave;à G&acirc;vres (à proximit&eacute; de Lorient- 56) - <b>Notre grand &eacute;v&egrave;nement de PRE-DÉPART !</b>',
            en: 'Saturday March 28th and Sunday March 29th - Maison Glaz in G&acirc;vres (near Lorient- 56) - <b>Our big pre-start event!</b>'
        }
    }
];

let updateFilter = radio => {
    console.log(`Update filter on ${radio}`);
    switch (radio.value) {
        case '1':
            console.log("No filter");
            fillOutFleet(null);
            break;
        case '2':
            console.log("Old boats");
            fillOutFleet(EX_BOAT);
            break;
        case '3':
            console.log("Passe-Coque Club");
            fillOutFleet(CLUB);
            break;
        case '4':
            console.log("&agrave; saisir");
            fillOutFleet(TO_GRAB);
            break;
        case '5':
            console.log("&agrave; vendre");
            fillOutFleet(FOR_SALE);
            break;
        case '6':
            console.log("Partenaires");
            fillOutFleet(PARTNERS);
            break;
        default:
            break;
    }
};

let getTheBoats = (filter, container, withBadge, pathPrefix) => {
    console.log(`getTheBoats, ${new Date().getTime()} ms`)
    if (THE_BOATS === null) {
        // let boatData = "/the_fleet.json";  // From a local json file
        let boatData = "/php/get_the_fleet.php"; // From the DB
        fetch(boatData, {
			method: "GET",
			headers: {
				    "Content-type": "application/json; charset=UTF-8"
			    }
		    }).then(response => {  // Warning... the NOT_FOUND error lands here, apparently.
                console.log(`Data Response: ${response.status} - ${response.statusText}`);
                if (response.status !== 200) { // Then there is a problem...
                    try {
                        response.text().then(txt => {
                            console.log(`Error text: ${txt}`);
                            // Use a custom alert
                            let errContent = (currentLang === 'FR') ?
                                            `Erreur &agrave; l'ex&eacute;cution de ${boatData}: ${response.statusText}.<br/>Le backup est utilis&eacute; &agrave; la place.<br/>Voyez votre administrateur.` :
                                            `Error executing ${boatData}: ${response.statusText}.<br/>Using backup data instead.<br/>See your admin.`;
                            showCustomAlert(errContent);
                        }, (error, errmess) => {
                            console.log(`${error}, ${errmess}`);
                        });
                    } catch (err) {
                        console.log(err);
                    }
                    console.log(`=> Origin: Client Side`);
                    THE_BOATS = THE_FLEET; // Using the backup list
                    populateBoatData(THE_BOATS, filter, container, withBadge, pathPrefix); // The actual display
                } else {
                    if (false) { // For debug: get raw text from the response
                        response.text().then(txt => {
                            console.log(txt);
                            try {
                                let json = JSON.parse(txt);
                                console.log(`=> Origin: ${json.origin}`);
                                THE_BOATS = json.data;
                            } catch (err) {
                                console.log(`Parsing error: ${err}`);
                                showCustomAlert(txt, false, true);
                                console.log(`=> Origin: Client Side`);
                                THE_BOATS = THE_FLEET; // Using the backup list
                            }
                            populateBoatData(THE_BOATS, filter, container, withBadge, pathPrefix); // The actual display
                        }, (error, errmess) => {
                            console.log(`${error}, ${errmess}`);
                        });
                    } else { // Default behavior, use JSON from THE_FLEET if it fails.
                        response.json().then(json => {
                            console.log(`data loaded, ${json.length} boat(s) from DB.`);
                            console.log(`=> Origin: ${json.origin}`);
                            THE_BOATS = json.data;
                            populateBoatData(THE_BOATS, filter, container, withBadge, pathPrefix); // The actual display
                        }, (error, errmess) => {
                            console.log(`Response to JSON: ${error},\nUsing BACKUP list`);
                            console.log(`=> Origin: Client Side`);
                            THE_BOATS = THE_FLEET; // Using the backup list
                            populateBoatData(THE_BOATS, filter, container, withBadge, pathPrefix); // The actual display
                        });
                    }
                }
            },
            (error, errmess) => {
                console.log("Ooch");
                let message;
                if (errmess) {
                    let mess = JSON.parse(errmess);
                    if (mess.message) {
                        message = mess.message;
                    }
                }
                console.debug("Failed to get code data..." + (error ? JSON.stringify(error, null, 2) : ' - ') + ', ' + (message ? message : ' - '));
                // Plus tard...
                THE_BOATS = THE_FLEET;
                // Using the backup list
                populateBoatData(THE_BOATS, filter, container, withBadge, pathPrefix); // The actual display
            });
    } else {
        populateBoatData(THE_BOATS, filter, container, withBadge, pathPrefix); // The actual display
    }
};

let populateBoatData = (boatList, filter, container, withBadge, pathPrefix) => {
    // Build new list
    let newList = [];
    // Sort by name ?
    if (boatList) {
        boatList.sort((a, b) => {
            if (a.name > b.name) {
                return 1;
            } else if (a.name < b.name) {
                return -1;
            }
            return 0;
        });
        // Filter here
        boatList.forEach(boat => {
            if (filter === null || boat.category == filter) {
                // console.log(`Filter ${filter}, adding ${boat.name}`);
                newList.push(boat);
            }
        });
    } else {
        console.log("boatList still null!!");
    }
    console.log(`Displaying ${newList.length} boats.`);
    // Populate. based on new list
    newList.forEach(boat => {
        let div = document.createElement('div');
        div.id = boat.id;
        div.classList.add("boat-image-plus-text");
        // div.style = "padding: 10px; z-index: 1; max-height: 420px; max-width: 300px;"; // See below. Make this class
        div.classList.add("boat-frame");
        // div.title = boat.name;
        div.onclick = function() { clickOnBoatPix(this, boat.name, pathPrefix); };
        div.onmouseover = function() { mouseOnTxPix(this); };
        let imgContainer = document.createElement('div');
        imgContainer.classList.add("boat-image-container");
        let img = document.createElement('img');
        img.src = `${pathPrefix}${boat.pix}`;
        // img.width = "100%";
        img.style.width = "100%";
        imgContainer.appendChild(img);
        div.appendChild(imgContainer);
        // Name and type
        let span = document.createElement('span');
        span.style = "position: relative; display: block; bottom: 4px; line-height: 1.1em;";
        span.innerHTML = `${boat.name}<br/>${boat.type}, ${boat.base}`;
        div.appendChild(span);
        // Badge
        if (withBadge) {
            let badge = document.createElement('div');
            badge.classList.add("badge");
            if (boat.category === EX_BOAT) {
                badge.classList.add("badge-old");
                badge.innerHTML = '<span style="font-size: 2.0em; background: transparent;">👋</span>'; // "Old<br/>boat";
            } else if (boat.category === CLUB) {
                badge.classList.add("badge-pc");
                badge.innerHTML = '<span>😎</span>'; // "PC<br/>Club";
            } else if (boat.category === TO_GRAB) {
                badge.classList.add("badge-grab");
                badge.innerHTML = '<span>🤩</span>'; // (currentLang === 'FR') ? "&Agrave;<br/>saisir" : "Grab<br/>it!";
            } else if (boat.category === FOR_SALE) {
                badge.classList.add("badge-for-sale");
                badge.innerHTML = '<span><img src="./logos/for.sale.png" style="width: 36px;"></span>'; // (currentLang === 'FR') ? "&Agrave;<br/>saisir" : "Grab<br/>it!";
            } else if (boat.category === PARTNERS) {
                badge.classList.add("badge-partners");
                badge.innerHTML = '<span>🤝</span>'; // (currentLang === 'FR') ? "&Agrave;<br/>saisir" : "Grab<br/>it!";
            }
            div.appendChild(badge);
        }
        container.appendChild(div);
    });
    console.log("Done with populateBoatData");
}

let fillOutFleet = (filter, containerId = 'fleet-container', withBadge = true, pathPrefix = '') => {

    let container = document.getElementById(containerId); // 'fleet-container');
    // drop all children
    while (container.hasChildNodes()) {
        container.removeChild(container.lastChild);
    }
    // Display, with the filter
    getTheBoats(filter, container, withBadge, pathPrefix);
};

let fillOutTheTeam = (containerId = 'team-container') => {

    let container = document.getElementById(containerId); // 'fleet-container');
    // drop all children
    while (container.hasChildNodes()) {
        container.removeChild(container.lastChild);
    }
    // Build new list
    let newList = [];
    let listToSort = [];
    // 1 - The boss on top
    THE_TEAM.forEach(member => {
        if (member.boss === true) {
            newList.push(member);
        } else {
            member.rnd = Math.random();
            listToSort.push(member);
        }
    });

    // Sort by rnd id
    listToSort.sort((a, b) => {
        if (a.rnd > b.rnd) {
            return 1;
        } else if (a.rnd < b.rnd) {
            return -1;
        }
        return 0;
    });
    listToSort.forEach(member => {
        newList.push(member);
    });
    console.log("Team is built!");
    // newList.forEach(tm => console.log(`Team: ${tm.label.fr}`));
    console.log(`Displaying ${newList.length} members.`);

    // <div class="pix-strip">
    let pixStrip = document.createElement("div");
    pixStrip.classList.add("pix-strip");
    container.appendChild(pixStrip);

    // Populate. based on newly sorted list
    newList.forEach(tm => {
        let div = document.createElement('div');

        div.id = tm.id;  // Team member div
        div.classList.add("image-plus-text");
        // div.title = boat.name;
        div.onclick = function() { aboutSomeone(this); };

        let img = document.createElement('img');
        img.src = tm.image;
        img.classList.add("board-image");
        div.appendChild(img);

        // Label
        let div2 = document.createElement('div');
        div2.style = "line-height: 1.2em;";
        div2.innerHTML =  currentLang === 'FR' ? tm.label.fr : tm.label.en;
        div.appendChild(div2);

        pixStrip.appendChild(div);
    });
    console.log("Done with fillOutTheTeam");
};

let updateInfoFilter = radio => {
    console.log(`Update filter on ${radio.value}`);
    switch (radio.value) {
        case 'all':
            console.log("No filter");
            fillOutActu(null);
            break;
        case 'agenda':
            console.log('Agenda');
            fillOutActu('agenda');
            break;
        case 'a2025':
            console.log("2025");
            fillOutActu('2025');
            break;
        case 'a2024':
            console.log("2024");
            fillOutActu('2024');
            break;
        case 'a2023':
            console.log("2023");
            fillOutActu('2023');
            break;
        case 'a2022':
            console.log("2022");
            fillOutActu('2022');
            break;
        case 'a2021':
            console.log("2021");
            fillOutActu('2021');
            break;
        case 'a2020':
            console.log("2020");
            fillOutActu('2020');
            break;
        case 'a2019':
            console.log("2019");
            fillOutActu('2019');
            break;
        case 'aComm':
            console.log("Communications");
            fillOutActu('communications'); // See INFO_SECTION
            break;
        case 'aNews':
            console.log("News");
            fillOutActu("news"); // See INFO_SECTION
            break;
        default:
            break;
    }
};

let interval = undefined;

function setAuto(id, cb) {
    console.log(`setAuto for ${id}, slider ${cb.id}, ${cb.checked}`);
    if (cb.checked) {
        interval = setInterval(() => {
            try {
                if (document.getElementById(id).offsetParent !== null) { // Check visibility
                    document.getElementById(id)._forward();
                } else {
                    clearInterval(interval);
                }
            } catch (err) {
                console.log(err);
                clearInterval(interval);
            }
        }, 5000);
    } else {
        clearInterval(interval);
    }
}

function onSlideShowClick(src) {
    console.log(">> Client side!! Slide " + src + " was clicked.");
    window.open(src, '_blank'); // This is an example
}

let fillOutActu = filter => {
    // Populate the div named actu-container
    let container = document.getElementById('actu-container');
    // drop all children
    while (container.hasChildNodes()) {
        container.removeChild(container.lastChild);
    }
    // Build new list
    let newList = [];
    // Filter here
    INFO_SECTION.forEach(section => {
        if (filter === null || section.section == filter) {
            console.log(`Filter ${filter}, adding '${section.section}'`);
            newList.push(section);
        }
    });
    console.log(`Displaying ${newList.length} section(s).`);
    newList.forEach(section => {
        // Create new div for this section
        let sectionDiv = document.createElement('div');
        // Now loop on sub-elements in the section
        section.content.forEach(event => {
            // title: "Passe-Coque Trophy"
            // content: "/actu/2023/passe-coque.trophy.html"
            console.log(`Adding event ${event.title}`);
            let eventDiv = document.createElement('div');
            eventDiv.style = "margin: 20px;";
            // sectionDiv.appendChild(eventDiv);
            console.log(`Now fetching ${event.content}`); // TODO Language !!
            fetch(event.content)
                .then(response => {  // Warning... the NOT_FOUND error lands here, apparently.
                    console.log(`Data Response: ${response.status} - ${response.statusText}`);
                    if (response.status !== 200) { // There is a problem...
                        eventDiv.innerHTML = generateFetchMessage(event.content, response); // `Fetching ${event.content}...<br/> Data Response: ${response.status} - ${response.statusText}<br/><b>En d&eacute;veloppement...<br/>Disponible prochainement.</b>`;
                    } else {
                        response.text().then(doc => {
                            // console.log(`${event.content} code data loaded, length: ${doc.length} (${doc}).`);
                            eventDiv.innerHTML = doc;
                        });
                    }
                    console.log("-- Done with fetch");
                },
                (error, errmess) => {
                    console.log("Ooch");
                    let message;
                    if (errmess) {
                        let mess = JSON.parse(errmess);
                        if (mess.message) {
                            message = mess.message;
                        }
                    }
                    console.debug("Failed to get code data..." + (error ? JSON.stringify(error, null, 2) : ' - ') + ', ' + (message ? message : ' - '));
                    // Plus tard...
                    eventDiv.innerHTML = generateFetchErrorMessage(contentName, error, errmess); // `<b>${contentName} ${currentLang === 'FR' ? ' introuvable...<br/>Bient&ocirc;t dispo !' : ' not found...<br/>Avai;able soon!'}</b>`;
                });
            sectionDiv.appendChild(eventDiv);
        });
        container.appendChild(sectionDiv);
    });
    console.log(`Done with Displaying ${newList.length} section(s).`);

    // Slideshows, onclick on images...
    try {
        document.getElementById("bpgo-lorient").slideclick = onSlideShowClick;
    } catch (err) {
        console.log(`SlideClick: ${err}`);
    }

};

let fillOutNextEvents = () => {
    console.log("Ah!!");
    // let container = document.getElementById('next-events-container');
    let ne = generateNextEvents();
    let list = document.getElementById('next-event-list');
    // Remove all childs
    while (list.firstChild) {
        list.removeChild(list.lastChild);
    }
    console.log(`There are ${ne.length} lines...`);
    // Add new ones
    ne.forEach(event => {
        let li = document.createElement('li');
        li.innerHTML = event;
        list.appendChild(li);
    });
}

// Dynamic translation, for the actu section.
// We assume all data are already in French.
let translate = (actuId) => {
    console.log(`${actuId} : ${(currentLang === 'FR') ? "En français" : "I'll speak english"}`);

    switch (actuId) {
        case 'cc-2023': // Carter Cup 2023
            if (currentLang === 'EN') {  // Then translate
                let dateField = document.getElementById('cc-2023').querySelector('h2');
                if (dateField) {
                    dateField.innerText = "Carter Cup, August 2023";
                }
                let contentField01 = document.getElementById('cc-2023').querySelector('#content-01');
                if (contentField01) {
                    contentField01.innerHTML = 'Jimmy, on board Pordin-Nancq, was a winner!';
                }
                // etc...
            }
            break;
        case 'fam-2023': // Festival dea Aventuries de la Mer 2023
            if (currentLang === 'EN') {  // Then translate
                let dateField = document.getElementById('fam-2023').querySelector('h2');
                if (dateField) {
                    dateField.innerText = "Festival des Aventuriers de la Mer, October 2023";
                }
                let contentField01 = document.getElementById('fam-2023').querySelector('#content-01');
                if (contentField01) {
                    contentField01.innerHTML = 'Passe-Coque wins the "Audelor et R&eacute;gion Bretagne" prize, and a 5000€ check, for the creation of the boat-club of the association.';
                }
                // etc...
            }
            break;
        case 'early-2024':
            if (currentLang === 'EN') {
                let dateField = document.getElementById('early-2024').querySelector('h2');
                if (dateField) {
                    dateField.innerText = "Mid-january 2024";
                }
                let contentField01 = document.getElementById('early-2024').querySelector('div');
                if (contentField01) {
                    contentField01.innerHTML = 'Release of the new web site.';
                }
            }
            break;
        default:
            break;
    }
};

let networkSubscribe = (type) => {
    alert(`Reaching ${type}.\nSoon.`);
};

let subscribeNewsLetter = () => {
    let userName = document.getElementById('first-last-name').value;
    let userEmail = document.getElementById('user-email').value;
    alert(`News Letter subscription for ${userName}, ${userEmail}`);
};

let scrollTheTeam = (dir) => {
    console.log(`Scrolling, ${dir}`);
    let container = document.getElementById("team-container");
    let nbPeople = container.querySelectorAll('div.image-plus-text').length;
    let step = container.clientWidth / nbPeople;
    container.scrollLeft += (step * dir);
};

let scrollPartners = (dir) => {
    console.log(`Scrolling, ${dir}`);
    let container = document.getElementById("partners-container");
    let nbPeople = container.querySelectorAll('div.partner-logo').length;
    let step = container.clientWidth / nbPeople;
    container.scrollLeft += (step * dir);
};

let openTab = (evt, tabName) => {
    let tabLinks = document.getElementsByClassName("tablinks"); // Tabs/Buttons

    for (let i=0; i<tabLinks.length; i++) {
        tabLinks[i].className = tabLinks[i].className.replace(" tab-active", ""); // Reset
    }
    let divSections = document.querySelectorAll(".tab-section");

    for (let i=0; i<divSections.length; i++) {
        divSections[i].style.display = (divSections[i].id === tabName) ? 'block' : 'none';
    }
    evt.currentTarget.className += " tab-active";
};

let openTab2 = (evt, tabName) => {
    let tabLinks = document.getElementsByClassName("tablinks-2"); // Tabs/Buttons

    for (let i=0; i<tabLinks.length; i++) {
        tabLinks[i].className = tabLinks[i].className.replace(" tab-active-2", ""); // Reset
    }
    let divSections = document.querySelectorAll(".tab-section-2");

    for (let i=0; i<divSections.length; i++) {
        divSections[i].style.display = (divSections[i].id === tabName) ? 'block' : 'none';
    }
    evt.currentTarget.className += " tab-active-2";
};

let reloadIF = (id) => {
    // console.log("Reloading iframe " + id);
    let iframe = document.getElementById(id);
    let src = iframe.src;
    iframe.src = src + '';
    return false;
};


let customAlertOpened = false;
// TODO With 'Copy Message'option
let showCustomAlert = (content, autoClose=true, preFmt=false) => {
    let customAlert = document.getElementById("custom-alert");
    document.getElementById('custom-alert-content').innerHTML = preFmt ? content : `<pre>${content}</pre>`;
    if (customAlert.show !== undefined) {
        customAlert.style.display = 'inline'; // For Safari...
        customAlert.show();
    } else {
        customAlert.style.display = 'inline';
    }
    customAlertOpened = true;
    window.setTimeout(closeCustomAlert, autoClose ? 5000 : 15000); // Close in 5, or 15s
};

let closeCustomAlert = () => {
    let customAlert = document.getElementById("custom-alert");
    if (customAlert.close !== undefined) {
        customAlert.style.display = 'none'; // Safari
        customAlert.close();
    } else {
        customAlert.style.display = 'none';
    }
    customAlertOpened = false;
};

// Optional
// window.alert = showCustomAlert;  // Not in window.onload !

let subscriptionOKMessage = () => {
    let mess = "Votre requête a été envoyée,\nvous êtes en copie du mail (vérifiez vos spams...).";
    if (currentLang !== 'FR') {
        mess = "Your request has been sent,\nyou're cc'd (check your spams...)."
    }
    return mess;
}

let subscriptionErrorMessage = () => {
    let mess = "Votre requ&ecirc;te a pos&eacute; un problème, elle n'est pas partie.";
    if (currentLang !== 'FR') {
        mess = "There was a problem posting your request...."
    }
    return mess;
}

let sendMessageOKMessage = () => {
    let mess = "Votre message a &eacute;t&eacute; envoy&eacute;,<br/>vous &ecirc;tes en copie (v&eacute;rifiez vos spams...).";
    if (currentLang !== 'FR') {
        mess = "Your message has been sent,<br/>you're cc'd (check your spams...)."
    }
    return mess;
}

let sendMessageErrorMessage = () => {
    let mess = "Votre message a pos&eacute; un probl&egrave;me, il n'est pas parti.";
    if (currentLang !== 'FR') {
        mess = "There was a problem posting your message...."
    }
    return mess;
}

let onSubscriptionResponse = (iframe) => {
    // console.log(iframe);
    let message = ''; // Error message
    try {
        message = iframe.contentDocument.querySelectorAll('body')[0].innerText.trim();
        console.log(`Response message (onSubscriptionResponse): ${message}`);
        if (message.startsWith("OK")) {
            message = "Votre Souscription a bien &eacute;t&eacute; enregistr&eacute;e.<br/>Message du server :<br/>" + message;
            if (currentLang == 'EN') {
                message = "Your subscription was successfull.<br/>Message from the server:<br/>" + message;
            }
        } else if (message.startsWith("ERROR")) {
            message = "Une erreur s'est produite (content dans le presse-papier) :<br/>" + message;
            if (currentLang == 'EN') {
                message = "An error happened (content in your clipboard):<br/>" + message;
            }
        }
    } catch (err) {
        console.log("Oops");
        try {
            message = iframe.contentDocument.querySelectorAll('body')[0].innerText.trim();
        } catch (err2) {
            console.log("No text, no error...");
        }
    }
    // Display in dialog, or custom alert.
    if (message.length > 0) {
        // alert(message);
        showCustomAlert(message, true);
        copyTextToClipboard(message); // All the time, error or not.
    }
}

let onSubmitResponse = (iframe, okMess, errorMess) => {
    // console.log(iframe);
    let message = ''; // error message
    try {
        message = iframe.contentDocument.querySelectorAll('body')[0].innerText.trim();
        if (message.length > 0) {  // because of the onload on the iframe...
            if (message.startsWith("ERROR")) {
                message = errorMess;
            } else {
                message = okMess;
            }
            // if (message.startsWith("OK")) {
            //     message = okMess;
            // } else if (message.startsWith("ERROR")) {
            //     message = errorMess;
            // }
        }
    } catch (err) {
        console.log("Oops");
        try {
            message = iframe.contentDocument.querySelectorAll('body')[0].innerText.trim();
        } catch (err2) {
            console.log("No text, no error...");
        }
    }
    // Display in dialog, or custom alert.
    if (message.length > 0) {
        // alert(message);
        showCustomAlert(message, true);
    }
};

let onSubscribeResponse = (iframe, okMess, errorMess) => {
    let message = '';
    try {
        message = iframe.contentDocument.querySelectorAll('body')[0].innerText.trim();
        if (message.length > 0) {  // because of the onload on the iframe...
            // console.log(`onSubscribeResponse: ${message}`);
            if (message.startsWith("ERROR")) {  // Sent by the email process
                message = errorMess;
            } else if (message.startsWith("PROCESS-ERROR:")) {  // Sent by the validation process
                let innerMess = message.substring("PROCESS-ERROR:".length).trim();
                if (innerMess.startsWith("SUBSCRIBE-001")) {
                    if (currentLang == 'FR') {
                        message = "Vous devez accepter les conditions de la charte.\nRe-essayez !";
                    } else {
                        message = "You need to agree with the chart. Try again!";
                    }
                } else if (innerMess.startsWith("SUBSCRIBE-002")) {
                    if (currentLang == 'FR') {
                        message = "Vous &ecirc;tes d&eacute;j&agrave; membre du boat-club.";
                    } else {
                        message = "You are already a Boat-Club member.";
                    }
                } else if (innerMess.startsWith("SUBSCRIBE-003")) {
                    if (currentLang == 'FR') {
                        message = "Vous devez &ecirc;tre membre de Passe-Coque pour pouvoir &ecirc;tre membre du Boat-Club.";
                    } else {
                        message = "You need to be a Passe-Coque member to be a Boat-Club member.";
                    }
                }
            } else {
                message = okMess;
            }
        }
    } catch (err) {
        console.log("Oops");
        try {
            message = iframe.contentDocument.querySelectorAll('body')[0].innerText.trim();
        } catch (err2) {
            console.log("No text, no error...");
        }
    }
    // Display in dialog, or custom alert.
    if (message.length > 0) {
        alert(decodeURIComponent(message));
        // showCustomAlert(message);
    }
};

let onResetPswdResponse = (iframe) => {
    // console.log(iframe);
    let message = '';
    try {
        message = iframe.contentDocument.querySelectorAll('body')[0].innerText.trim();
        // console.log(`Response message: ${message}`);
        if (message.startsWith("OK")) {
            message = "Votre demande a bien &eacute;t&eacute; enregistr&eacute;e. Un email vous a &eacute;t&eacute; envoy&eacute;.";
            if (currentLang !== 'FR') {
                message = "Your request was successfull. An email was sent.";
            }
        } else if (message.startsWith("ERROR")) {
            message = "Une erreur s'est produite... " . message;
            if (currentLang !== 'FR') {
                message = "An error happened... " . message;
            }
        }
    } catch (err) {
        console.log("Oops");
        try {
            message = iframe.contentDocument.querySelectorAll('body')[0].innerText.trim();
        } catch (err2) {
            console.log("No text, no error...");
        }
    }
    // Display in dialog, or custom alert.
    if (message.length > 0) {
        // alert(message);
        showCustomAlert(message, true);
    }
}

/**
 * For the messages in both 1_xx.html and 61_xx.html
 * Same field ids.
 *
 * @param {*} evt
 * @returns true if OK, false otherwise (to prevent submit)
 */
let checkFields = (evt) => {
    let ok = true;
    let errMess = [];

    let lastName, firstName, fullName;

    if (document.getElementById('last-name')) { // Several possible origins...
        lastName = document.getElementById('last-name').value;
        firstName = document.getElementById('first-name').value;
    } else {
        fullName = document.getElementById('first-last-name').value;
    }

    let email = document.getElementById('user-email').value;
    if (!fullName) {
        if (!lastName || lastName.trim().length === 0) {
            errMess.push(currentLang === 'FR' ? 'On a besoin d\'un nom.' : 'Last name is required.');
        }
        if (!firstName || firstName.trim().length === 0) {
            errMess.push(currentLang === 'FR' ? 'On a besoin d\'un pr&eacute;nom.' : 'First name is required.');
        }
    } else {
        if (fullName.trim().length === 0) {
            errMess.push(currentLang === 'FR' ? 'On a besoin d\'un nom.' : 'Name is required.');
        }
    }
    if (!email || email.trim().length === 0) {
        errMess.push(currentLang === 'FR' ? 'On a besoin d\'un email.' : 'Email is required.');
    }
    if (errMess.length > 0) {
        ok = false;
        let mess = '';
        errMess.forEach(el => mess += (el + '<br/>'));
        let prefix = (currentLang === 'FR' ? 'Votre message n\'a pas &eacute;t&eacute; envoy&eacute;.<br/>' : 'You message was not sent.<br/>');
        showCustomAlert(`<pre>${prefix}${mess}</pre>`);
    }
    return ok;
};

// Copy the content of a field
let copyToClipboard = (fieldId) => {
    let value = document.getElementById(fieldId).innerHTML;
    let codeContent = value.replaceAll("<br>", "\n");
    codeContent = content.replaceAll("<br/>", "\n");
    // console.log(codeContent);
    let codeHolder = document.createElement("textarea"); // To keep the format, 'input' would not.
    codeHolder.value = codeContent;
    document.body.appendChild(codeHolder);
    codeHolder.select();
    document.execCommand("copy");
    document.body.removeChild(codeHolder);
    // customAlert(`Value ${value} copied to clipboard`);
};

// Copy a literal value
let copyTextToClipboard = (content) => {
    let codeContent = content.replaceAll("<br>", "\n");
    codeContent = content.replaceAll("<br/>", "\n");
    // console.log(codeContent);
    let codeHolder = document.createElement("textarea"); // To keep the format, 'input' would not.
    codeHolder.value = codeContent;
    document.body.appendChild(codeHolder);
    codeHolder.select();
    document.execCommand("copy");
    document.body.removeChild(codeHolder);
    // customAlert(`Value ${content} copied to clipboard`);
};

let keepScrolling = true;

function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
};

let onPartnerSlidesLoad = () => {
    console.log('Partners, loaded!');
    let parentDiv = document.getElementById("partners-container");
    let pixStrip = document.querySelector(".pix-strip");
    if (pixStrip) {
        let scrollMargin = 0;
        let max = pixStrip.scrollWidth - parentDiv.clientWidth + 10;
        let timeout = 10;
        let inc = 1;
        console.log('PixStrip was found.');
        let scrollDiv = (margin) => {
            pixStrip.style.marginLeft = `-${margin}px`; // TODO Try scrollLeft ?
            pixStrip = document.querySelector(".pix-strip"); // Still there (still on the partner page)...
            if (pixStrip) {
                window.setTimeout(() => {
                    if (keepScrolling) {
                        scrollMargin += inc;
                        // console.log(`margin now ${scrollMargin} / ${max} ...`);
                        if (scrollMargin > max) {
                            // scrollMargin = 0;
                            inc = -1;
                            sleep(2000).then(() => {
                                console.log('Resuming after sleep...'); // Wow !
                                scrollDiv(scrollMargin);
                            });
                        } else if (scrollMargin < 0) {
                            inc = 1;
                            sleep(2000).then(() => {
                                console.log('Resuming after sleep...'); // Wow !
                                scrollDiv(scrollMargin);
                            });
                        } else {
                            scrollDiv(scrollMargin);
                        }
                    } else {
                        scrollDiv(scrollMargin); // For resumeScroll.
                    }
                }, timeout);
            } else {
                console.log('Partner screen was left...');
            }
        };
        scrollDiv(scrollMargin); // First start
    }
};

let stopScroll = () => {
    console.log("Stop scrolling...");
    keepScrolling = false;
};

let resumeScroll = () => {
    console.log("Resume scrolling...");
    keepScrolling = true;
};

// let mouseOnTop = (el) => {
//     console.log(`Mouse on ${el.id}`);
//     // el.style.cursor = 'pointer';
// }

// Jump to somewhere else in the same site...
let jumpTo = (page, extraPrm) => {
    let origin = window.location.origin;
    let newUrl = origin + `?lang=${currentLang}&nav-to=${page}&${extraPrm}`
    console.log(`Going to ${newUrl}`);
    // debugger;
    if (false) {
        window.open(newUrl); // New window
    } else {
        window.location.assign(newUrl); // Same window
    }
};

function projectFilterWasPressed(event) {
    // debugger;
    // console.log(event.key);
    if (event.key === "Enter") {
        // Cancel the default action, if needed
        // event.preventDefault();
        searchProjects();
    }
}

// Filters ?
function searchProjects() {
    filterProjectsOn('project-container', 'nb-prj');
    filterProjectsOn('project-container-expired', 'nb-prj-exp');
}

function filterProjectsOn(divId, nbDivId) {
    let projectContainer = document.getElementById(divId);
    let projectArray = projectContainer.querySelectorAll("div.boat-frame");

    let valueToLookFor = document.getElementById("search-field").value.trim();

    let nbPrjSelected = 0;

    if (valueToLookFor.trim().length > 0) {
        console.log(`Looking for "${valueToLookFor}"`);
        // produceSearchList(valueToLookFor);
        projectArray.forEach(prjNode => {
            console.log(`Scanning project ${prjNode.getAttribute('id')}...`);
            let keywords = prjNode.getAttribute('keywords'); // Not standard attribute.
            let matching = false;
            if (keywords && keywords.length > 0) {
                keywordsArray = keywords.split(';');
                keywordsArray.forEach(kw => {
                    if (kw.trim().length > 0) {
                        if (valueToLookFor.match(kw.trim()) || kw.trim().toUpperCase().includes(valueToLookFor.toUpperCase())) {
                            console.log(`-  "${valueToLookFor}" matches "${kw.trim()}"`);
                            matching = true;
                        }
                    }
                });
            }
            if (!matching) {
                prjNode.style.display = 'none';
            } else {
                prjNode.style.display = 'inline'; // Restore, in case of previous filter
                nbPrjSelected += 1;
            }
        });
    } else {
        console.log("Nothing to look for...");
        projectArray.forEach(prjNode => {
            prjNode.style.display = 'inline';
            nbPrjSelected += 1;
        });
    }
    // Update project number
    document.getElementById(nbDivId).innerHTML = (currentLang == 'FR') ? `${nbPrjSelected} projet(s) selectionn&eacute;(s).` :
                                                                         `${nbPrjSelected} project(s) selected.`;
}

// For the global filters, in search (in the menu)
const KEYWORDS = [
	{
		name: 'Meteo',
		keywords: [ 'meteo', 'météo', 'weather', 'forecast', 'prevision', 'prévision' ],
		url: '/tech.and.nav/meteo.php',
		comment: 'Sites de previsions meteo.'
	},
	{
		name: 'Marees',
		keywords: [ 'maree', 'marée', 'almanac', 'tide', 'tidal' ],
		url: '/tech.and.nav/tides.es6/leaflet.tide.stations.html',
		comment: 'Marées dans le monde.'
	},
	{
		name: 'Publication d\'almanachs',
		keywords: [ 'astro' , 'maree', 'marée', 'almanac', 'publication', 'publish' ],
		url: '/?nav-to=7&goto=almanacs',
		comment: 'Divers almanachs.'
	},
	{
		name: 'Couture',
		keywords: [ 'couture' , 'sewing', 'chutes', 'leftover' ],
		url: '/tech.and.nav/couture.and.co/index.html',
		comment: 'Recycler les chutes !'
	},
	{
		name: 'Vie de l\'asso',
		keywords: [ 'asso' , 'vie asso', 'vie de l\'asso', 'bourse', 'bourses', 'chantier', 'bricolage', 'bricoler', 'equipier', 'équipier', 'convoyage', 'agenda', 'planning', 'statut', 'status' ],
		url: '/?nav-to=68',
		comment: ''
	},
	{
		name: 'Le Chantier',
		keywords: [ 'chantier', 'bricolage', 'bricoler', 'travail', 'travaux', 'shipyard', 'saint', 'philibert' ],
		url: '/?nav-to=32',
		comment: 'À Saint Philibert'
	},
	{
		name: 'Passe-Coque',
		keywords: [ 'asso', 'association', 'status', 'statut' ],
		url: '/?nav-to=67',
		comment: 'Status de l\'association, à partir de votre espace membres'
	},
	{
		name: 'Passe-Coque',
		keywords: [ 'todo', 'list', 'travaux', 'bateau', 'work', 'boats' ],
		url: '/?nav-to=67',
		comment: 'TODO Lists, travaux à faire sur les bateaux, à partir de votre espace membres'
	},
	{
		name: 'Adhésion',
		keywords: [ 'asso', 'association', 'adherer' , 'adhérer', 'adhesion', 'adhésion', 'subscribe', 'join', 'cotisation', 'fee', 'don', 'gift', 'membre', 'membership' ],
		url: '/?nav-to=51',
		comment: 'Rejoignez l\'asso Passe-Coque'
	},
	{
		name: 'Faire un don',
		keywords: [ 'join', 'cotisation', 'fee', 'don', 'gift' ],
		url: '/?nav-to=52',
		comment: 'Soutenez l\'asso Passe-Coque'
	},
	{
		name: 'Formations / Trainings',
		keywords: [ 'formation', 'training', 'boutique', 'store', 'shop' ],
		url: '/?nav-to=64',
		comment: 'Boutique, et catalogue des formations'
	},
	{
		name: 'Nav Astro, Sextant',
		keywords: [ 'formation', 'training', 'sextant', 'astro', 'nav', 'celestial' ],
		url: '/?nav-to=68',
		comment: 'Tout en bas de la page'
	},
	{
		name: 'Assemblées générales',
		keywords: [ 'assemblee generale', 'assemblée générale', 'general meeting', 'ag' ],
		url: '/?nav-to=67',
		comment: 'Accessibles par votre espace membre'
	},
	{
		name: 'Actualités',
		keywords: [ 'actualite', 'actualité', 'news', 'agenda', 'planning', 'events', 'event', 'spi', 'ouest', 'ouest france', 'ouest-france', 'rubi' ],
		url: '/?nav-to=62',
		comment: 'Toute l\'actualité'
	},
	{
		name: 'News Letters',
		keywords: [ 'actualite', 'actualité', 'news', 'letter', 'newsletter', 'news-letter', 'news-letters', 'newsletters', 'semaphore', 'sémaphore' ],
		url: '/?nav-to=62&where=news-letters',
		comment: 'Toute les news-letters'
	},
	{
		name: 'Boat Club',
		keywords: [ 'boat club', 'boat-club', 'club' ],
		url: '/boat-club/?nav-to=1',
		comment: 'Boat-Club Passe-Coque'
	},
	{
		name: 'Boat Club',
		keywords: [ 'boat club', 'boat-club', 'club' ],
		url: '/?nav-to=33',
		comment: 'Description du Boat-Club Passe-Coque'
	},
	// Projet(s) ?
	{
		name: 'Projets',
		keywords: [ 'projet', 'projects' ],
		url: '/?nav-to=31',
		comment: 'Tous les projets Passe-Coque'
	},
	{
		name: 'Fabuleuse Armada',
		keywords: [ 'armada', 'fabuleuse', 'handicap' ],
		url: '/?nav-to=62&where=armada-2024',
		comment: 'La Fabuleuse Armada, 2025'
	},
	{
		name: 'Anita Conti',
		keywords: [ 'anita' , 'conti', 'reveuse', 'rêveuse', 'marseille', 'lorient', 'fecamp', 'fécamp', 'sillage', 'wake', 'leenan', 'head', 'durable', 'peche', 'pêche' ],
		url: '/?nav-to=31&tx=35',
		comment: 'Dans le sillage d\'Anita Conti'
	},
	{
		name: 'Voiles Vagabondes',
		keywords: [ 'vagabonde', 'lawyer', 'juriste', 'engage', 'engagee', 'engagé', 'engagée', 'voiles', 'vagabondes', 'voiles vagabondes', 'voile' ],
		url: '/?nav-to=31&tx=38',
		comment: 'Projet Voiles Vagabondes'
	},
	{
		name: 'Passages',
		keywords: [ 'theatre' , 'theater', 'théatre', 'lorient', '56', 'hydrophone', 'radio', 'balise', 'sauvegarde', 'passage', 'passages' ],
		url: '/?nav-to=31&tx=34',
		comment: 'Projet Passages'
	},
	{
		name: 'Passe-Coque Trophy',
		keywords: [ 'trophy', 'trophée', 'trophe', 'passe', 'coque', 'passe-coque', 'passe coque' ],
		url: '/?nav-to=31&tx=33',
		comment: 'Projet Passe-Coque Trophy'
	},
	{
		name: 'Projet Jericho',
		keywords: [ 'boat', 'bateau', 'jericho', 'carter', 'acier', 'steel' ],
		url: '/?nav-to=31&tx=32',
		comment: 'un Carter à restaurer.'
	},
	{
		name: 'Plusieurs Arpèges',
		keywords: [ 'arpège', 'arpege', 'choral' ],
		url: '/?nav-to=31&tx=31',
		comment: 'Projet Arpèges'
	},
	{
		name: 'De Cap en Cap',
		keywords: [ 'cap', 'benoiton', 'gin', 'fizz', 'nomadict', 'difficulte', 'difficulté', 'jeune' ],
		url: '/?nav-to=31&tx=30',
		comment: 'Projet De Cap en Cap'
	},
	{
		name: 'Les Jeannettes',
		keywords: [ 'solidaire', 'social', 'artistique', 'jeannette', 'shadok', 'victime', 'violence' ],
		url: '/?nav-to=31&tx=29',
		comment: 'Projet Les Jeannettes'
	},
	{
		name: 'Rubi\'s Cup 2024',
		keywords: [ 'rubi', 'godille', 'port', 'tudy', 'cooking', 'sculling' ],
		url: '/?nav-to=31&tx=28',
		comment: 'Projet Rubi\'s Cup'
	},
	{
		name: 'Shazzan, Patagonie',
		keywords: [ 'shazzan', 'patagonie', 'patagonia', 'puerto williams' ],
		url: '/?nav-to=31&tx=39',
		comment: 'Projet Shazzan'
	},
	{
		name: 'Rubi\'s Cup 2025',
		keywords: [ 'rubi', 'godille', 'port', 'tudy', 'sculling', 'touline', 'cooking' ],
		url: '/?nav-to=31&tx=36',
		comment: 'Projet Rubi\'s Cup'
	},
	{
		name: 'Fêtes Maritimes, Brest 2024',
		keywords: [ 'fete', 'fête', 'maritime', 'brest', '2024' ],
		url: '/?nav-to=31&tx=27',
		comment: 'Projet Brest 2024'
	},
	{
		name: 'Projet La Tête à Toto',
		keywords: [ 'low' , 'tech', 'raspberry', 'no tech', 'no-tech', 'low-tech', 'zero', 'emission', 'tete', 'tête', 'toto' ],
		url: '/?nav-to=31&tx=26',
		comment: 'Zero emission.'
	},
	{
		name: 'Projet Philovent',
		keywords: [ 'godille', 'scull', 'benoiton' ],
		url: '/?nav-to=31&tx=25',
		comment: 'Pour Philippe'
	},
	{
		name: 'Refit du Super-Arlequin "Saudade"',
		keywords: [ 'super', 'arlequin', 'saudade', 'magazine', 'voile magazine', 'fx', 'crecy' ],
		url: '/?nav-to=31&tx=24',
		comment: 'Projet porté par Voile Magazine'
	},
	{
		name: 'Hublow',
		keywords: [ 'tiny house', 'tiny', 'habitat', 'leger', 'léger', 'insolite', 'hublow', 'brio', 'jeff', 'flow' ],
		url: '/?nav-to=31&tx=23',
		comment: 'A l\'origine de Flow'
	},
	{
		name: 'Jolly Jumper',
		keywords: [ 'handicap', 'handivoile', 'ffv', 'jolly jumper', 'jolly', 'jumper', 'sables d\'olonne' ],
		url: '/?nav-to=31&tx=22',
		comment: ''
	},
	{
		name: 'National Muscadet 2024',
		keywords: [ 'national', 'muscadet', '2024', 'lorient' ],
		url: '/?nav-to=31&tx=21',
		comment: ''
	},
	{
		name: 'Low-Tech projects',
		keywords: [ 'low' , 'tech', 'raspberry', 'no tech', 'no-tech', 'low-tech', 'opensource', 'open', 'source' ],
		url: '/?nav-to=31&tx=20',
		comment: 'Plusieurs projets Low-Tech.'
	},
	{
		name: 'GRETA',
		keywords: [ 'greta', 'lycee', 'lycée' ],
		url: '/?nav-to=31&tx=18',
		comment: 'Relations avec les Lycées et GRETA'
	},
	{
		name: 'Cap Martinique',
		keywords: [ 'cap', 'martinique', 'benoiton', 'philippe' ],
		url: '/?nav-to=31&tx=17',
		comment: 'Avec Philippe Benoiton'
	},
	{
		name: 'Isse\'O',
		keywords: [ 'handicap', 'insertion', 'inclusion', 'professionel', 'social' ],
		url: '/?nav-to=31&tx=16',
		comment: 'Inclusion sociale et professionnelle des personnes en situation de handicap par la pratique de la voile'
	},
	{
		name: 'Projet Entendre la Mer',
		keywords: [ 'handicap' , 'entendre', 'listen', 'the sea', 'la mer', 'tiago', 'evasion', 'melkart', 'malentendant', 'sourd', 'deaf' ],
		url: '/?nav-to=31&tx=09',
		comment: 'A bord de Melkart'
	},
	{
		name: 'Afri\'Can',
		keywords: [ 'prothese', 'prothèse', 'felicie', 'félicie' ],
		url: '/?nav-to=31&tx=08',
		comment: 'Projet Afri\'Can'
	},
	{
		name: 'Objectif Grand Sud',
		keywords: [ 'cardinale', 'chili', 'argentine', 'puerto williams', 'horn', 'antarctique', 'bernard', 'ravignan' ],
		url: '/?nav-to=31&tx=07',
		comment: 'À bord de "La Cardinale"'
	},
	{
		name: 'Asso Pompiers',
		keywords: [ 'asso', 'pompier', 'etel' ],
		url: '/?nav-to=31&tx=06',
		comment: ''
	},
	{
		name: 'Pordin-Nancq',
		keywords: [ 'carter', 'carter 37', 'pordin', 'pordin-nancq', 'pordin nancq' ],
		url: '/?nav-to=31&tx=05',
		comment: 'Project Pordin-Nancq, Carter 37'
	},
	{
		name: 'WATTer',
		keywords: [ 'watt', 'watter', 'reveuse', 'rêveuse' ],
		url: '/?nav-to=31&tx=04',
		comment: 'Projet WATTer, avec La Rêveuse'
	},
	{
		name: 'Cap Melvan',
		keywords: [ 'cap', 'meklvan', 'karate', 'karaté' ],
		url: '/?nav-to=31&tx=03',
		comment: ''
	},
	{
		name: 'Voyage en Patrimoine',
		keywords: [ 'passpartout', 'patrimoine', 'voyage' ],
		url: '/?nav-to=31&tx=02',
		comment: ''
	},
	{
		name: 'Nav\'Solidaire',
		keywords: [ 'nav', 'solidaire', 'prothese', 'prothèse', 'afrique', 'gambie', 'senegal', ,'sénégal' ],
		url: '/?nav-to=31&tx=01',
		comment: 'À bord de "La Rêveuse"'
	},
	// Bateau(x) ?
	{
		name: 'La Flotte',
		keywords: [ 'bateau', 'boat', 'flotte', 'fleet' ],
		url: '/?nav-to=4',
		comment: 'Tous les bateaux'
	},
	{
		name: 'La Maoa',
		keywords: [ 'boat', 'bateau', 'maoa', 'la-maoa', 'plate', 'riguidel' ],
		url: '/?nav-to=4&boat-id=la-maoa',
		comment: 'Plate de Saint Philibert'
	},
	{
		name: 'Nuage',
		keywords: [ 'boat', 'bateau', 'nuage', 'soling', 'erik' ],
		url: '/?nav-to=4&boat-id=nuage',
		comment: 'Soling'
	},
	{
		name: 'Coquina',
		keywords: [ 'boat', 'bateau', 'coquina', 'nathaniel', 'herreshoff' , 'vivier', 'godille', 'aviron', 'etel', 'cat', 'ketch', 'sculling' ],
		url: '/?nav-to=4&boat-id=coquina',
		comment: 'Canot voile aviron'
	},
	{
		name: 'Eh\'Tak',
		keywords: [ 'boat', 'bateau', 'eh tak', 'eh-tak' , 'ehtak', 'eh\'tak', 'shipman', 'shipman 28', 'etel' ],
		url: '/?nav-to=4&boat-id=eh-tak',
		comment: 'Shipman 28.'
	},
	{
		name: 'Le Brio',
		keywords: [ 'boat', 'bateau', 'brio', 'flow', 'hublow', 'jeff', 'flow', 'tiny house', 'tiny', 'habitat', 'leger', 'léger', 'insolite' ],
		url: '/?nav-to=4&boat-id=brio',
		comment: 'Brio, a l\'origine du project Hublow.'
	},
	{
		name: 'Tanikel',
		keywords: [ 'delph 32', 'ciotat', 'tanikel' ],
		url: '/?nav-to=4&boat-id=tanikel',
		comment: 'Delph 32'
	},
	{
		name: 'Shadok',
		keywords: [ 'solidaire', 'social', 'artistique', 'jeannette', 'shadok', 'victime', 'violence' ],
		url: '/?nav-to=4&boat-id=shadok',
		comment: 'Via 42'
	},
	{
		name: 'Jericho',
		keywords: [ 'boat', 'bateau', 'jericho', 'carter', 'acier', 'steel' ],
		url: '/?nav-to=4&boat-id=jericho',
		comment: 'Le bateau, un Carter à restaurer.'
	},
	{
		name: 'Le Melkart',
		keywords: [ 'handicap' , 'entendre', 'listen', 'the sea', 'la mer', 'tiago', 'evasion', 'melkart' ],
		url: '/?nav-to=4&boat-id=melkart',
		comment: 'Evasion 32'
	},
	{
		name: 'Pordin Nancq',
		keywords: [ 'boat', 'bateau', 'pordin', 'pordin-nancq' , 'carter', 'carter 37' ],
		url: '/?nav-to=4&boat-id=pordin-nancq',
		comment: 'Le bateau, Carter 37.'
	},
	{
		name: 'Anao',
		keywords: [ 'boat', 'bateau', 'folie douce', 'anao' ],
		url: '/?nav-to=4&boat-id=anao',
		comment: 'Le bateau, Folie Douce'
	},
	{
		name: 'Ar Mor Van',
		keywords: [ 'boat', 'bateau', 'mor van', 'kelt 620', 'greta', 'lycee', 'lycée' ],
		url: '/?nav-to=4&boat-id=ar-mor-van',
		comment: 'Le bateau, Kelt 620'
	},
	{
		name: 'Araben',
		keywords: [ 'boat', 'bateau', 'araben', 'mauric', 'one off' ],
		url: '/?nav-to=4&boat-id=araben',
		comment: 'Le bateau, One off Mauric'
	},
	{
		name: 'Atlantide',
		keywords: [ 'boat', 'bateau', 'atlantide', 'gibsea', '33', 'pompier' ],
		url: '/?nav-to=4&boat-id=atlantide',
		comment: 'Le bateau, GibSea 33'
	},
	{
		name: 'Avel Mad',
		keywords: [ 'boat', 'bateau', 'avel mad', 'mousquetaire', 'herbulot' ],
		url: '/?nav-to=4&boat-id=avel-mad',
		comment: 'Le bateau, Mousquetaire'
	},
	{
		name: 'Bémol III',
		keywords: [ 'boat', 'bateau', 'bemol', 'sun rise' ],
		url: '/?nav-to=4&boat-id=bemol',
		comment: 'Le bateau, Sun Rise 35'
	},
	{
		name: 'Babou',
		keywords: [ 'boat', 'bateau', 'babou', 'catamaran', 'flying phantom' ],
		url: '/?nav-to=4&boat-id=babou',
		comment: 'Le bateau, Flying Phantom'
	},
	{
		name: 'Coevic 2',
		keywords: [ 'boat', 'bateau', 'coevic', 'mirage 28', 'lorient' ],
		url: '/?nav-to=4&boat-id=coevic-2',
		comment: 'Le bateau, Mirage 28'
	},
	{
		name: 'Coraxy',
		keywords: [ 'boat', 'bateau', 'coraxy', 'guy', 'cognac', 'harlé', 'harle' ],
		url: '/?nav-to=4&boat-id=coraxy',
		comment: 'Le bateau, Cognac'
	},
	{
		name: 'Félicie',
		keywords: [ 'boat', 'bateau', 'felicie', 'félicie', 'presles' ],
		url: '/?nav-to=4&boat-id=felicie',
		comment: 'Le bateau, One off Presles'
	},
	{
		name: 'Gwenillig',
		keywords: [ 'boat', 'bateau', 'gwenellig', 'Eygthene 24', 'bernard', 'bene', 'benny' ],
		url: '/?nav-to=4&boat-id=gwenillig',
		comment: 'Le bateau, Eygthene 24'
	},
	{
		name: 'Shazzan',
		keywords: [ 'boat', 'bateau', 'shazzan', 'shazan', 'patagonie', 'patagonia', 'puerto williams', 'puerto', 'williams', 'alu', 'aluminium' ],
		url: '/?nav-to=4&boat-id=shazzan',
		comment: 'Le bateau, OVNI 435'
	},
	{
		name: 'Ia Orana',
		keywords: [ 'boat', 'bateau', 'ia orana', 'iaorana', 'milord' ],
		url: '/?nav-to=4&boat-id=ia-orana',
		comment: 'Le bateau, Milord'
	},
	{
		name: 'Iapix',
		keywords: [ 'boat', 'bateau', 'iapix', 'offshore 35' ],
		url: '/?nav-to=4&boat-id=iapix',
		comment: 'Le bateau, Offshore 35'
	},
	{
		name: 'Imagine',
		keywords: [ 'boat', 'bateau', 'imagine', 'selection', '37' ],
		url: '/?nav-to=4&boat-id=imagine',
		comment: 'Le bateau, Selection 37'
	},
	{
		name: 'Jolly Jumper',
		keywords: [ 'boat', 'bateau', 'jolly', 'jumper', 'first 325', 'sables d\'olonne' ],
		url: '/?nav-to=4&boat-id=jolly-jumper',
		comment: 'Le bateau, First 325'
	},
	{
		name: 'Jules Verne',
		keywords: [ 'boat', 'bateau', 'jules', 'verne', 'sir 520' ],
		url: '/?nav-to=4&boat-id=jules-verne',
		comment: 'Le bateau, SIR 520'
	},
	{
		name: 'L\'Heure Bleue',
		keywords: [ 'boat', 'bateau', 'heure bleue', 'arpege', 'arpège' ],
		url: '/?nav-to=4&boat-id=heure-bleue',
		comment: 'Le bateau, Arpège'
	},
	{
		name: 'La Rêveuse',
		keywords: [ 'boat', 'bateau', 'reveuse', 'rêveuse', 'damien 40', 'acier' ],
		url: '/?nav-to=4&boat-id=la.reveuse',
		comment: 'Le bateau, Damien 40'
	},
	{
		name: 'Lady of Solent',
		keywords: [ 'boat', 'bateau', 'lady of solent', 'contessa', 'contessa 35' ],
		url: '/?nav-to=4&boat-id=los',
		comment: 'Le bateau, Contessa 35'
	},
	{
		name: 'Lohengrin',
		keywords: [ 'boat', 'bateau', 'lohengrin', 'ketch', 'acier' ],
		url: '/?nav-to=4&boat-id=lohengrin',
		comment: 'Le bateau, Ketch en acier'
	},
	{
		name: 'Ma Enez',
		keywords: [ 'boat', 'bateau', 'ma enez', 'symphonie', 'alain' ],
		url: '/?nav-to=4&boat-id=ma-enez',
		comment: 'Le bateau, Symphonie'
	},
	{
		name: 'Manu Oviri',
		keywords: [ 'boat', 'bateau', 'manu', 'oviri', 'manu oviri', 'comanche', 'comanche 32', 'etel', 'regis', 'régis' ],
		url: '/?nav-to=4&boat-id=manu-oviri',
		comment: 'Le bateau, Comanche 32'
	},
	{
		name: 'Melvan',
		keywords: [ 'boat', 'bateau', 'melvan', 'karate', 'karate 33', 'karaté', 'karaté 33' ],
		url: '/?nav-to=4&boat-id=melvan',
		comment: 'Le bateau, Karaté 33'
	},
	{
		name: 'Mirella',
		keywords: [ 'boat', 'bateau', 'mirella', 'maica', 'maica 12.50' ],
		url: '/?nav-to=4&boat-id=mirella',
		comment: 'Le bateau, Maica 12.50'
	},
	{
		name: 'Nomaddict',
		keywords: [ 'boat', 'bateau', 'nomadict', 'nomaddict', 'gin fizz', 'gin', 'fizz' ],
		url: '/?nav-to=4&boat-id=nomadict',
		comment: 'Le bateau, Gin Fizz'
	},
	{
		name: 'Passpartout',
		keywords: [ 'boat', 'bateau', 'passpartout', 'lorient', 'acier' ],
		url: '/?nav-to=4&boat-id=passpartout',
		comment: 'Le bateau, One off, en acier'
	},
	{
		name: 'Penny Lane',
		keywords: [ 'boat', 'bateau', 'panny lane', 'arpege', 'arpège', 'penny', 'lane', 'penny-lane' ],
		url: '/?nav-to=4&boat-id=penny-lane',
		comment: 'Le bateau, Arpège'
	},
	{
		name: 'Remora',
		keywords: [ 'boat', 'bateau', 'remora', 'arcachonnais' ],
		url: '/?nav-to=4&boat-id=remora',
		comment: 'Le bateau, Arcachonnais'
	},
	{
		name: 'Rozen an Avel',
		keywords: [ 'boat', 'bateau', 'rozen an avel', 'arpege', 'arpège' ],
		url: '/?nav-to=4&boat-id=rozen-an-avel',
		comment: 'Le bateau, Arpège'
	},
	{
		name: 'Saigane',
		keywords: [ 'boat', 'bateau', 'saigane', 'dufour', 'dufour 2800' ],
		url: '/?nav-to=4&boat-id=saigane',
		comment: 'Le bateau, Dufour 2800'
	},
	{
		name: 'Saudade',
		keywords: [ 'boat', 'bateau', 'arlequin', 'super', 'super arlequin', 'voile mag', 'voile magazine', 'fx', 'crecy' ],
		url: '/?nav-to=4&boat-id=saudade',
		comment: 'Le bateau, Super Arlequin'
	},
	{
		name: 'Stiren ar Mor',
		keywords: [ 'boat', 'bateau', 'stiren', 'stiren ar mor', 'ghibli', 'michel' ],
		url: '/?nav-to=4&boat-id=stiren',
		comment: 'Le bateau, Ghibli'
	},
	{
		name: 'Taapuna',
		keywords: [ 'boat', 'bateau', 'taapuna', 'edel', 'edel 660' ],
		url: '/?nav-to=4&boat-id=taapuna',
		comment: 'Le bateau, Edel 660'
	},
	{
		name: 'Tokad 2',
		keywords: [ 'boat', 'bateau', 'tokad', 'neptune', 'neptune 99' ],
		url: '/?nav-to=4&boat-id=tokad-2',
		comment: 'Le bateau, Neptune 99'
	},
	{
		name: 'Trehudal',
		keywords: [ 'boat', 'bateau', 'trehudal', 'nicholson', 'nicholson 33' ],
		url: '/?nav-to=4&boat-id=trehudal',
		comment: 'Le bateau, Nicholson 33'
	},
	{
		name: 'Tri Yann',
		keywords: [ 'boat', 'bateau', 'tri yann', 'trimaran', 'allegro' ],
		url: '/?nav-to=4&boat-id=tri-yann',
		comment: 'Le bateau, Trimaran Allegro'
	},
	{
		name: 'Twist Again',
		keywords: [ 'boat', 'bateau', 'twist again', 'twist', 'again', 'jod', 'jod 35' ],
		url: '/?nav-to=4&boat-id=twist-again',
		comment: 'Le bateau, JOD 35'
	},
	{
		name: 'Velona',
		keywords: [ 'boat', 'bateau', 'velona', 'old gaffer' ],
		url: '/?nav-to=4&boat-id=velona',
		comment: 'Le bateau, Old Gaffer'
	},
	{
		name: 'Wanita Too',
		keywords: [ 'boat', 'bateau', 'wanita', 'wanita too', 'first class', 'first class 12' ],
		url: '/?nav-to=4&boat-id=wanita',
		comment: 'Le bateau, First Class 12'
	},
	{
		name: 'Zéphir',
		keywords: [ 'boat', 'bateau', 'zephir', 'zéphir', 'lucas', 'birvidic' ],
		url: '/?nav-to=4&boat-id=zephir',
		comment: 'Le bateau, Birvidic 700'
	},
    // Team members ?
	{
		name: 'Pierre-Jean',
		keywords: [ 'pj', 'pierre-jean', 'pierre', 'jean', 'pierre jean', 'jannin', 'president', 'président', 'ceo' ],
		url: '/?nav-to=21&who=pj',
		comment: 'Pierre-Jean Jannin, président de Passe-Coque'
	},
    {
		name: 'Alain',
		keywords: [ 'alain', 'hahusseau', 'directgeur', 'director', 'technique', 'technic', 'technical', 'cto' ],
		url: '/?nav-to=21&who=alain',
		comment: 'Alain Hahusseau, directeur technique de Passe-Coque'
	},
    {
		name: 'Anne',
		keywords: [ 'anne', 'auroux', 'communication', 'comm' ],
		url: '/?nav-to=21&who=anne',
		comment: 'Anne Auroux, Communication'
	},
    {
		name: 'Bernard',
		keywords: [ 'bene', 'benny', 'objectif', 'grand', 'sud', 'cardinale', 'grand', 'large' ],
		url: '/?nav-to=21&who=bernard',
		comment: 'Bernard de Ravignan, expert Grand Large'
	},
    {
		name: 'Catherine',
		keywords: [ 'kasha', 'secretaire', 'secretary', 'catherine', 'laguerre' ],
		url: '/?nav-to=21&who=catherine',
		comment: 'Catherine Laguerre'
	},
    {
		name: 'Guy & Gab',
		keywords: [ 'guy', 'gab', 'boat', 'club', 'boat club', 'boat-club' ],
		url: '/?nav-to=21&who=gng',
		comment: 'Développement du Boat Club'
	},
    {
		name: 'Jeff',
		keywords: [ 'jeff', 'jean francois', 'jean françois', 'comm', 'hublow', 'flow', 'eco village', 'eco-village', 'brio' ],
		url: '/?nav-to=21&who=jeff',
		comment: 'Jean-François Allais, Eco Village'
	},
    {
		name: 'Michel',
		keywords: [ 'michel', 'lemeni', 'leméni', 'photo' ],
		url: '/?nav-to=21&who=michel',
		comment: 'Michel Leméni, photos'
	},
    {
		name: 'Olivier',
		keywords: [ 'olivier', 'le diouris', 'diouris', 'tech', 'web', 'raspberry', 'astro', 'sextant' ],
		url: '/?nav-to=21&who=olivier',
		comment: 'Olivier Le Diouris, web, tech, no-tech, low-tech, etc.'
	},
    {
		name: 'Régis',
		keywords: [ 'régis', 'regis', 'germain', 'greta', 'tresorier', 'trésorier', 'cfo' ],
		url: '/?nav-to=21&who=regis',
		comment: 'Régis Germain, trésorier de Passe-Coque'
	},
    {
		name: 'Stéphane',
		keywords: [ 'stephane', 'stéphane', 'menuet', 'ffv' ],
		url: '/?nav-to=21&who=stephane',
		comment: 'Stéphane Menuet, monde sportif, course au large'
	}

];

// "Enter" in the field (not the button)
function filterWasPressed(event) {
    // debugger;
    // console.log(event.key);
    if (event.key === "Enter") {
        // Cancel the default action, if needed
        // event.preventDefault();
        findIt();
    }
}

// Filters ?
function findIt() {
    filterOn();
}

function createListElement(name, url, comment) {
	let li = document.createElement('li');
	let a = document.createElement('a');
	a.href = url;
	// a.target = '_blank';
	a.appendChild(document.createTextNode(`${name} - ${comment}`));
	li.appendChild(a);

	return li;
}

function filterOn() {
    let valueToLookFor = document.getElementById("search-field").value.trim();

    let nbItemsSelected = 0;

	let foundElements = [];

    if (valueToLookFor.trim().length > 0) {
        console.log(`Looking for "${valueToLookFor}", among ${KEYWORDS.lemgth} items.`);
        // produceSearchList(valueToLookFor);
        KEYWORDS.forEach(searchItem => {
            console.log(`Scanning ${searchItem.name}...`);
            let keywordsArray = searchItem.keywords;
            let matching = false;
            if (keywordsArray.length > 0) {
                keywordsArray.forEach(kw => {
                    if (kw.trim().length > 0) {
                        if (valueToLookFor.match(kw.trim()) || kw.trim().toUpperCase().includes(valueToLookFor.toUpperCase())) {
                            console.log(`-  "${valueToLookFor}" matches "${kw.trim()}"`);
                            matching = true;
                        }
                    }
                });
            }
            if (!matching) {
                // Nope
            } else {
                let url = searchItem.url;
                if (searchItem.url.startsWith("/") && (searchItem.url.includes("nav-to=") || searchItem.url.includes("tx=") || searchItem.url.includes("who="))) { // boat-id, etc...
                    url += `&lang=${currentLang}`;
                }

                foundElements.push(createListElement(searchItem.name, url, searchItem.comment));
                nbItemsSelected += 1;
            }
        });
    } else {
        console.log("Nothing to look for...");
		// C'est un peu vague...
		// Display all the site
		foundElements.push(createListElement('Passe-Coque', `/?lang=${currentLang}`, 'Le site de Passe-Coque'));
        nbItemsSelected += 1;
    }

	// Append foundElements
	if (nbItemsSelected > 0) {
		let elementList = document.createElement('ul');
		foundElements.forEach(child => {
			elementList.appendChild(child);
		});
		let suggestedList = document.getElementById('suggested-list');
        if (suggestedList) {
            suggestedList.innerHTML = (currentLang === 'FR') ? '<h3>R&eacute;sultats</h3>' : '<h3>Results</h3>';
            // while (suggestedList.childElementCount > 0) {
            //     suggestedList.removeChild(suggestedList.childNodes[0]);
            // }
            suggestedList.appendChild(  // Results
                elementList
            );
        } // else suggested-list not found ?
	} else {
		let suggestedList = document.getElementById('suggested-list');
        if (suggestedList) {
            while (suggestedList.childElementCount > 0) {
                suggestedList.removeChild(suggestedList.childNodes[0]);
            }
            suggestedList.innerHTML = (currentLang === 'FR') ? 'Rien trouv&eacute;...' : 'Nothing was found...';
        }
	}

}
