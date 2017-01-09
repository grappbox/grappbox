using Grappbox.Helpers;
using Grappbox.HttpRequest;
using Grappbox.Model;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Globalization;
using Windows.UI.Popups;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Media.Imaging;
using Windows.Web.Http;

namespace Grappbox.ViewModel
{
    public class UserSettingsViewModel : ViewModelBase
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

        //public async System.Threading.Tasks.Task getProjectLogo()
        //{
        //    await model.LogoUpdate();
        //    await model.SetLogo();
        //    NotifyPropertyChanged("Avatar");
        //}

        public async System.Threading.Tasks.Task updateAPI(string password = null, string oldPassword = null)
        {
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
            HttpResponseMessage res = await HttpRequestManager.Put(props, "user");
            if (res.IsSuccessStatusCode)
            {
                model = SerializationHelper.DeserializeJson<UserSettingsModel>(await res.Content.ReadAsStringAsync());
                notifyAll();

                ContentDialog cd = new ContentDialog();
                cd.Title = "Success";
                cd.Content = HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync());
                cd.HorizontalContentAlignment = Windows.UI.Xaml.HorizontalAlignment.Center;
                cd.VerticalContentAlignment = Windows.UI.Xaml.VerticalAlignment.Center;
                var t = cd.ShowAsync();
                await System.Threading.Tasks.Task.Delay(TimeSpan.FromSeconds(1.5));
                t.Cancel();
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
            props.Clear();
        }

        public async System.Threading.Tasks.Task getAPI()
        {
            HttpResponseMessage res = await HttpRequestManager.Get(null, "user");
            Debug.WriteLine(await res.Content.ReadAsStringAsync());
            if (res.IsSuccessStatusCode)
            {
                model = SerializationHelper.DeserializeJson<UserSettingsModel>(await res.Content.ReadAsStringAsync());
                Debug.WriteLine(model.Birthday);
                notifyAll();
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync()));
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

        public DateTime Birthday
        {
            get { if (model == null || model.Birthday == null) return DateTime.Today; DateTime name = DateTime.Parse(model.Birthday); if (name != null) { return name; } else return DateTime.Today; }
            set
            {
                if (value != DateTime.Parse(model.Birthday))
                {
                    model.Birthday = value.ToString("YYYY-mm-dd");
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