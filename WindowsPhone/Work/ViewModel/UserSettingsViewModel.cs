using GrappBox.ApiCom;
using GrappBox.Model;
using GrappBox.View;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using Windows.Web.Http;
using Windows.Storage.Streams;
using Windows.UI.Popups;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Media.Imaging;

namespace GrappBox.ViewModel
{
    class UserSettingsViewModel : ViewModelBase
    {
        static private UserSettingsViewModel instance = null;
        private UserSettingsModel model = new UserSettingsModel();

        static public UserSettingsViewModel GetViewModel()
        {
            if (instance != null)
                return instance;
            else return new UserSettingsViewModel();
        }
        public UserSettingsViewModel()
        {
            instance = this;
        }

        public async System.Threading.Tasks.Task getProjectLogo()
        {
            await model.LogoUpdate();
            await model.SetLogo();
            NotifyPropertyChanged("Avatar");
        }

        public async System.Threading.Tasks.Task updateAPI(string password = null, string oldPassword = null)
        {
            ApiCommunication api = ApiCommunication.Instance;
            Dictionary<string, object> props = new Dictionary<string, object>();

            if (model.Firstname != null && model.Firstname != "")
                props.Add("firstname", model.Firstname);
            if (model.Lastname != null && model.Lastname != "")
                props.Add("lastname", model.Lastname);
            if (model.Birthday != null)
                props.Add("birthday", model.Birthday);
            if (model.av != null && model.av != "")
                props.Add("avatar", model.av);
            if (password != null && oldPassword != null)
            {
                props.Add("password", password);
                props.Add("oldPassword", oldPassword);
            }
            if (model.Phone != null && model.Phone != "")
                props.Add("phone", model.Phone);
            if (model.Country != null && model.Country != "")
                props.Add("country", model.Country);
            if (model.Linkedin != null && model.Linkedin != "")
                props.Add("linkedin", model.Linkedin);
            if (model.Viadeo != null && model.Viadeo != "")
                props.Add("viadeo", model.Viadeo);
            if (model.Twitter != null && model.Twitter != "")
                props.Add("twitter", model.Twitter);
            HttpResponseMessage res = await api.Put(props, "user/basicinformations/" + User.GetUser().Token);
            if (res.IsSuccessStatusCode)
            {
                model = api.DeserializeJson<UserSettingsModel>(await res.Content.ReadAsStringAsync());
                notifyAll();

                ContentDialog cd = new ContentDialog();
                cd.Title = "Success";
                cd.Content = api.GetErrorMessage(await res.Content.ReadAsStringAsync());
                cd.HorizontalContentAlignment = Windows.UI.Xaml.HorizontalAlignment.Center;
                cd.VerticalContentAlignment = Windows.UI.Xaml.VerticalAlignment.Center;
                var t = cd.ShowAsync();
                await System.Threading.Tasks.Task.Delay(TimeSpan.FromSeconds(1.5));
                t.Cancel();
            }
            else {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
            props.Clear();
        }

        public async System.Threading.Tasks.Task getAPI()
        {
            ApiCommunication api = ApiCommunication.Instance;
            object[] token = { User.GetUser().Token };
            HttpResponseMessage res = await api.Get(token, "user/basicinformations");
            Debug.WriteLine(await res.Content.ReadAsStringAsync());
            if (res.IsSuccessStatusCode)
            {
                model = api.DeserializeJson<UserSettingsModel>(await res.Content.ReadAsStringAsync());
                notifyAll();
            }
            else {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        private void notifyAll()
        {
            NotifyPropertyChanged("Firstname");
            NotifyPropertyChanged("Lastname");
            NotifyPropertyChanged("Birthday");
            NotifyPropertyChanged("Email");
            NotifyPropertyChanged("Phone");
            NotifyPropertyChanged("Country");
            NotifyPropertyChanged("Linkedin");
            NotifyPropertyChanged("Viadeo");
            NotifyPropertyChanged("Twitter");
        }

        #region ModelBindedPropertiesNotifiers
        public string Firstname
        {
            get { if (model == null) return ""; string name = model.Firstname; if (name != null) { return name; } else return ""; }
            set
            {
                if (value != model.Firstname)
                {
                    model.Firstname = value;
                    NotifyPropertyChanged("Firstname");
                }
            }
        }

        public string Lastname
        {
            get { if (model == null) return ""; string name = model.Lastname; if (name != null) { return name; } else return ""; }
            set
            {
                if (value != model.Lastname)
                {
                    model.Lastname = value;
                    NotifyPropertyChanged("Lastname");
                }
            }
        }

        public DateTime? Birthday
        {
            get { if (model == null) return DateTime.Today; DateTime? name = model.Birthday; if (name != null) { return name; } else return DateTime.Today; }
            set
            {
                if (value != model.Birthday)
                {
                    model.Birthday = value;
                    NotifyPropertyChanged("Birthday");
                }
            }
        }

        public string Email
        {
            get { if (model == null) return ""; string name = model.Email; if (name != null) { return name; } else return ""; }
        }

        public string Phone
        {
            get { if (model == null) return ""; string name = model.Phone; if (name != null) { return name; } else return ""; }
            set
            {
                if (value != model.Phone)
                {
                    model.Phone = value;
                    NotifyPropertyChanged("Phone");
                }
            }
        }

        public string Country
        {
            get { if (model == null) return ""; string name = model.Country; if (name != null) { return name; } else return ""; }
            set
            {
                if (value != model.Country)
                {
                    model.Country = value;
                    NotifyPropertyChanged("Country");
                }
            }
        }

        public BitmapImage Avatar
        {
            get
            {
                if (model == null)
                    return new BitmapImage();
                return model.Avatar;
            }
        }

        public string avatar
        {
            set
            {
                if (value != model.av)
                {
                    model.av = value;
                    NotifyPropertyChanged("Avatar");
                }
            }
        }

        public string Linkedin
        {
            get { if (model == null) return ""; string name = model.Linkedin; if (name != null) { return name; } else return ""; }
            set
            {
                if (value != model.Linkedin)
                {
                    model.Linkedin = value;
                    NotifyPropertyChanged("Linkedin");
                }
            }
        }

        public string Viadeo
        {
            get { if (model == null) return ""; string name = model.Viadeo; if (name != null) { return name; } else return ""; }
            set
            {
                if (value != model.Viadeo)
                {
                    model.Viadeo = value;
                    NotifyPropertyChanged("Viadeo");
                }
            }
        }

        public string Twitter
        {
            get { if (model == null) return ""; string name = model.Twitter; if (name != null) { return name; } else return ""; }
            set
            {
                if (value != model.Twitter)
                {
                    model.Twitter = value;
                    NotifyPropertyChanged("Twitter");
                }
            }
        }
        #endregion ModelBindedPropertiesNotifiers
    }
}
