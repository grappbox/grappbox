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
using Windows.UI.Popups;

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

        public WhiteBoardListModel ObjectSelect { get; set; }
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
            props.Add("projectId", id);
            props.Add("whiteboardName", name);
            HttpResponseMessage res = await api.Post(props, "whiteboard/new");
            if (res.IsSuccessStatusCode)
            {
                WhiteBoardListModel wlm = api.DeserializeJson<WhiteBoardListModel>(await res.Content.ReadAsStringAsync());
                Whiteboards.Add(wlm);
            }
            else
            {
                Debug.WriteLine(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
            }
        }

        public async System.Threading.Tasks.Task DeleteWhiteboard()
        {
            if (ObjectSelect != null)
            {
                ApiCommunication api = ApiCommunication.Instance;

                object[] token = { User.GetUser().Token, ObjectSelect.Id };
                HttpResponseMessage res = await api.Delete(token, "whiteboard/delete");
                if (res.IsSuccessStatusCode)
                {
                    _whiteboards.Remove(ObjectSelect);
                    NotifyPropertyChanged("Whiteboards");
                    ObjectSelect = null;
                }
                else
                {
                    MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                    await msgbox.ShowAsync();
                }
            }
        }
    }
}
