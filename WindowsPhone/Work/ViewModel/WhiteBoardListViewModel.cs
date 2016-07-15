using GrappBox.ApiCom;
using GrappBox.Model;
using GrappBox.Ressources;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Diagnostics;
using System.Linq;
using Windows.Web.Http;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Input;
using Windows.UI.Xaml.Controls;

namespace GrappBox.ViewModel
{
    class WhiteBoardListViewModel : ViewModelBase
    {
        private ObservableCollection<WhiteBoardListModel> _whiteboards;
        public ObservableCollection<WhiteBoardListModel> Whiteboards
        {
            get { return _whiteboards; }
            set { _whiteboards = value;  NotifyPropertyChanged("Whiteboards"); }
        }
        public WhiteBoardListViewModel()
        {
        }
        private ICommand _tapList;
        public ICommand TapList
        {
            get { return _tapList ?? (_tapList = new CommandHandler(TapListAction)); }
        }
        private void TapListAction()
        {}
        public async System.Threading.Tasks.Task GetWhiteboards()
        {
            ApiCommunication api = ApiCommunication.Instance;
            int id = SettingsManager.getOption<int>("ProjectIdChoosen");
            object[] token = { User.GetUser().Token, id };
            HttpResponseMessage res = await api.Get(token, "whiteboard/list");
            if (res.IsSuccessStatusCode)
            {
                Debug.WriteLine(await res.Content.ReadAsStringAsync());
                Whiteboards = api.DeserializeArrayJson<ObservableCollection<WhiteBoardListModel>>(await res.Content.ReadAsStringAsync());
                NotifyPropertyChanged("Whiteboards");
            }
            else
            {
                Debug.WriteLine(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
            }
        }
        public async System.Threading.Tasks.Task CreateWhiteboard(string name)
        {
            ApiCommunication api = ApiCommunication.Instance;
            int id = SettingsManager.getOption<int>("ProjectIdChoosen");
            Dictionary<string, object> props = new Dictionary<string, object>();
            props.Add("token", User.GetUser().Token);
            props.Add("projectId", User.GetUser().Token);
            props.Add("whiteboardName", User.GetUser().Token);
            HttpResponseMessage res = await api.Post(props, "whiteboard/new");
            if (res.IsSuccessStatusCode)
            {
                Debug.WriteLine(await res.Content.ReadAsStringAsync());
                WhiteBoardListModel wlm = api.DeserializeJson<WhiteBoardListModel>(await res.Content.ReadAsStringAsync());
                Whiteboards.Add(wlm);
            }
            else
            {
                Debug.WriteLine(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
            }
        }
    }
}
