using GrappBox.ApiCom;
using GrappBox.Model;
using GrappBox.View;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Net.Http;
using Windows.Storage.Streams;
using Windows.UI.Xaml.Media.Imaging;

namespace GrappBox.ViewModel
{
    class UserSettingsViewModel : ViewModelBase
    {
        static private UserSettingsViewModel instance = null;
        private UserSettingsModel model;

        static public UserSettingsViewModel GetUserSettingsViewModel()
        {
            return instance;
        }
        public UserSettingsViewModel()
        {
            instance = this;
        }

        public async void updateAPI(string password = null)
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            Dictionary<string, object> props = new Dictionary<string, object>();

            if (model.Firstname != null && model.Firstname != "")
                props.Add("firstname", model.Firstname);
            if (model.Lastname != null && model.Lastname != "")
                props.Add("lastname", model.Lastname);
            if (model.Birthday != null)
                props.Add("birthday", model.Birthday);
            if (model.Avatar != null && model.Avatar != "")
                props.Add("avatar", model.Avatar);
            if (password != null)
                props.Add("password", password);
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
                UserView.GetUser().affMessage(false, "Update Successful");
            }
            else {
                Debug.WriteLine(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                UserView.GetUser().affMessage(true, "Update Fail: " + api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
            }
            props.Clear();
        }

        public async void getAPI()
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            object[] token = { User.GetUser().Token };
            HttpResponseMessage res = await api.Get(token, "user/basicinformations");
            if (res.IsSuccessStatusCode)
            {
                model = api.DeserializeJson<UserSettingsModel>(await res.Content.ReadAsStringAsync());
                notifyAll();
            }
            else {
                Debug.WriteLine(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
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
            NotifyPropertyChanged("Avatar");
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
            get { if (model == null) return DateTime.Today; DateTime name = model.Birthday; if (name != null) { return name; } else return DateTime.Today; }
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
                string base64 = model.Avatar;
                if (base64 == null || base64 == "")
                    return new BitmapImage();
                else
                {
                    var imageBytes = Convert.FromBase64String(base64);
                    using (InMemoryRandomAccessStream ms = new InMemoryRandomAccessStream())
                    {
                        using (DataWriter writer = new DataWriter(ms.GetOutputStreamAt(0)))
                        {
                            writer.WriteBytes((byte[])imageBytes);
                            writer.StoreAsync().GetResults();
                        }

                        var image = new BitmapImage();
                        image.SetSource(ms);
                        return image;
                    }
                }
            }
        }

        public string avatar
        {
            set
            {
                if (value != model.Avatar)
                {
                    model.Avatar = value;
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
