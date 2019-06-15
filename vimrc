" syntax
syntax on

" history : how many lines of history VIM has to remember
set history=2000

" filetype
filetype on
" Enable filetype plugins
filetype plugin on
" filetype indent on

" base
set nocompatible                " don't bother with vi compatibility
set autoread                    " reload files when changed on disk, i.e. via `git checkout`
set shortmess=atI

set magic                       " For regular expressions turn magic on
set title                       " change the terminal's title
set nobackup                    " do not keep a backup file

set novisualbell                " turn off visual bell
set noerrorbells                " don't beep
set visualbell t_vb=            " turn off error beep/flash
set t_vb=
set tm=500

" show location
set cursorcolumn
set cursorline

" movement
set scrolloff=7                 " keep 3 lines when scrolling

" show
set ruler                       " show the current row and column
"set number                      " show line numbers
set nowrap
set showcmd                     " display incomplete commands
set showmode                    " display current modes
set showmatch                   " jump to matches when entering parentheses
set matchtime=2                 " tenths of a second to show the matching parenthesis

" search
set hlsearch                    " highlight searches
set incsearch                   " do incremental searching, search as you type
set ignorecase                  " ignore case when searching
set smartcase                   " no ignorecase if Uppercase char present

" tab
" set expandtab                   " expand tabs to spaces
set smarttab
set shiftround

" indent
set shiftround
set shiftwidth=4
set tabstop=4
set softtabstop=4                " insert mode tab and backspace use 4 spaces

" encoding
set encoding=utf-8
set fileencodings=ucs-bom,utf-8,cp936,gb18030,big5,euc-jp,euc-kr,latin1
set termencoding=utf-8
set ffs=unix,dos,mac
set formatoptions+=m
set formatoptions+=B

" select & complete
set selection=inclusive
set selectmode=mouse,key

set completeopt=longest,menu
set wildmenu                           " show a navigable menu for tab completion"
set wildmode=longest,list,full
set wildignore=*.o,*~,*.pyc,*.class

" others
set backspace=indent,eol,start  " make that backspace key work the way it should
set whichwrap+=<,>,h,l

set background=dark
colorscheme desert

" set mark column color
hi! link SignColumn   LineNr
hi! link ShowMarksHLl DiffAdd
hi! link ShowMarksHLu DiffChange

" status line
set statusline=%<%f\ %h%m%r%=%k[%{(&fenc==\"\")?&enc:&fenc}%{(&bomb?\",BOM\":\"\")}]\ %-14.(%l,%c%V%)\ %P
set laststatus=2   " Always show the status line - use 2 lines for the status bar

autocmd FileType python set tabstop=4 shiftwidth=4 expandtab
autocmd BufRead,BufNew *.md,*.mkd,*.markdown  set filetype=markdown.mkd

" disable auto wrap and auto comments
set formatoptions-=cro 

" https://github.com/ryanpcmcquen/fix-vim-pasting
let &t_SI .= "\<Esc>[?2004h"
let &t_EI .= "\<Esc>[?2004l"

inoremap <special> <expr> <Esc>[200~ XTermPasteBegin()

function! XTermPasteBegin()
    set pastetoggle=<Esc>[201~
    set paste
    return ""
endfunction

