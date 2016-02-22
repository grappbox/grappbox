using GrappBox.View;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Input;
using Windows.UI.Xaml.Controls;

namespace GrappBox.ViewModel
{
    class MenuViewModel : ViewModelBase
    {
        private ICommand _menuCommandNavigation;
        public ICommand MenuCommandNavigation
        {
            get { return _menuCommandNavigation ?? (_menuCommandNavigation = new CommandHandler<MenuEnum>(MenuNavigationAction)); }
        }
        void MenuNavigationAction(MenuEnum e)
        {
            Page page;
            switch (e)
            {
                case MenuEnum.DASHBOARD:
                    page = new DashBoardView();
                    break;
                case MenuEnum.WHITEBOARD_LIST:
                    page = new WhiteBoardListView();
                    break;
                case MenuEnum.USER_SETTINGS:
                    break;
                case MenuEnum.PROJECT_SETTING:
                    break;
                case MenuEnum.CALENDAR:
                    break;
                case MenuEnum.GANTT:
                    break;
                case MenuEnum.CLOUD:
                    break;
                case MenuEnum.TIMELINE:
                    break;
                case MenuEnum.BUGTRACKER:
                    break;
                default:
                    break;
            }
        }
    }
}
