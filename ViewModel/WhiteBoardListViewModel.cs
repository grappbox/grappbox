using GrappBox.Model;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Input;
using Windows.UI.Xaml.Controls;

namespace GrappBox.ViewModel
{
    class WhiteBoardListViewModel : ViewModelBase
    {
        private Page _currentPage;
        public Page CurrentPage
        {
            get { return _currentPage; }
            set { _currentPage = value; NotifyPropertyChanged("CurrentPage"); }
        }
        public List<String> WhiteBoards
        {
            get { return model.WhiteBoards; }
            set { model.WhiteBoards = value; NotifyPropertyChanged("WhiteBoards"); }
        }
        public List<String> Projects
        {
            get { return model.Projects; }
            set { model.Projects = value; NotifyPropertyChanged("Projects"); }
        }
        private WhiteBoardListModel model;
        public WhiteBoardListViewModel()
        {
            model = new WhiteBoardListModel();
        }
        private ICommand _tapList;
        public ICommand TapList
        {
            get { return _tapList ?? (_tapList = new CommandHandler(TapListAction)); }
        }
        private void TapListAction()
        {}
    }
}
