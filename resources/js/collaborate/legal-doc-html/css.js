export default `
#tinymce [data-end-of-volume] {
  border-top: 2px dashed #000000;
  padding-top: 1.25rem;
}

#tinymce [data-end-of-volume]:before {
  content: "END OF VOLUME " attr(data-end-of-volume);
  color: #be9518;
  display: inline-block;
  font-size: 9px;
  position: absolute;
  top: -2px;
  left: 12px;
  letter-spacing: 2px;
}
#tinymce > p {
  position: relative;
}
span[data-inline] {
  position: relative;
}
span[data-inline="num"] {
  background: #64B77582;
  border-right: 3px solid #11414a82;
  border-left: 3px solid #00d9ff82;
  padding-right:5px;
  font-family: "Courier New", Courier, "Lucida Sans Typewriter", "Lucida Typewriter", monospace;
}
span[data-inline="heading"] {
  background: #fbf0cf82;
  border-right: 3px solid #b1933a;
  border-left: 3px solid #837d6a;
  font-size: 1.1em;
  font-family: "Lucida Grande", "Lucida Sans Unicode", "Lucida Sans", Geneva, Verdana, sans-serif;
}
span[data-inline="term"] {
  border: 1px dashed rgb(139, 45, 151);
  font-family: "Century Gothic", CenturyGothic, AppleGothic, sans-serif;
  padding: 2px;
}
span[data-inline]:hover:after {
  content: attr(data-inline);
  position: absolute;
  top: 130%;
  left: 50%;
  transform: translateX(-50%);
  border-width: 5px;
  border-style: solid;
  border-color: #78bfcc transparent transparent transparent;
  background-color: #78bfcc;
  color: #fff;
  text-align: center;
  border-radius: 6px;
  padding: 0;
  z-index: 100;
}

.mce-visualblocks p[data-level] {
  border: 1px dashed #ececec;
}

.mce-visualblocks p[data-level]:after {
  content: attr(data-level);
  position: absolute;
  bottom: 10px;
  left: -10px;
  font-size: 10px;
  color: #63b5c5;
}

p[data-type]:before, table[data-type]:before {
  content: attr(data-type) attr(data-content);
  color: #be9518;
  display: inline-block;
  font-size: 9px;
  position: absolute;
  top: -2px;
  left: 12px;
  letter-spacing: 2px;
}
p[data-type="note"]:before {
  content: attr(data-type);
  color: #fff;
  background: #1a2433;
  border-radius: 3px;
  padding: 1px 3px;
}
p[data-type=none]:before, table[data-type=none]:before {
  content: "";
}
.mce-visualblocks p {
  border-color: transparent;
}
.mce-visualblocks p[id], .mce-visualblocks table[id] {
  border-color: #64B775;
}

.indentation-error {
  color: red;
}

p[data-inline]:before {
  content: attr(data-inline);
  color: #63b5c5;
  display: inline-block;
  font-size: 8px;
  position: absolute;
  bottom: -15px;
}
p[data-override]:before {
  color: #cf2e5d;
}
p[data-name]:before {
  content: attr(data-name) !important;
  color: #63b5c5;
  display: inline-block;
  font-size: 8px;
  position: absolute;
  bottom: -15px;
}
p[data-error]:before {
  content: attr(data-error) !important;
  color: #cf2e5d;
  display: inline-block;
  font-size: 8px;
  position: absolute;
  bottom: -15px;
  left: 0;
}
span[data-inline="noteRef"] {
  vertical-align: super;
  font-size: 75%;
}
`;
