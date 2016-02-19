using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Input;
using Windows.UI.Xaml.Controls;
using GrappBox.View;

namespace GrappBox.ViewModel
{
    class DashBoardViewModel : ViewModelBase
    {
        private ICommand _menuCommandNavigation;
        public ICommand MenuCommandNavigation
        {
            get { return _menuCommandNavigation ?? (_menuCommandNavigation = new CommandHandler<MenuEnum>(MenuNavigationAction)); }
        }
        void MenuNavigationAction(MenuEnum e)
        {
            Frame tmp = new Frame();
            if (e == MenuEnum.WHITEBOARD)
            {
                var page = new WhiteBoardView();
            }
        }
    }
}