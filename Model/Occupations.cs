using Grappbox.HttpRequest;
using Grappbox.Model.Global;
using Grappbox.Resources;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Linq;
using System.Runtime.InteropServices.WindowsRuntime;
using System.Text;
using System.Threading.Tasks;
using Windows.Storage;
using Windows.Storage.Streams;
using Windows.Web.Http;

namespace Grappbox.Model
{
    public class Occupations
    {
        public class OccupationUser
        {
            private string _id;
            [JsonProperty("id")]
            public string Id
            {
                get { return _id; }
                set { _id = value; }
            }
            private string _firstName;
            [JsonProperty("firstName")]
            public string FirstName
            {
                get { return _firstName; }
                set { _firstName = value; }
            }
            private string _lastName;
            [JsonProperty("lastName")]
            public string LastName
            {
                get { return _lastName; }
                set { _lastName = value; }
            }
        }
        public string UserName
        {
            get { return User.FirstName + " " + User.LastName; }
        }
        private string _name;
        [JsonProperty("name")]
        public string Name
        {
            get { return _name; }
            set { _name = value; }
        }
        private OccupationUser _user;
        [JsonProperty("user")]
        public OccupationUser User
        {
            get { return _user; }
            set { _user = value; }
        }
        private string _occupation;
        [JsonProperty("occupation")]
        public string Occupation
        {
            get { return _occupation; }
            set { _occupation = value; }
        }
        private int _tasks_begun;
        [JsonProperty("number_of_tasks_begun")]
        public int Tasks_begun
        {
            get { return _tasks_begun; }
            set { _tasks_begun = value; }
        }
        private int _tasks_ongoing;
        [JsonProperty("number_of_ongoing_tasks")]
        public int Tasks_Ongoing
        {
            get { return _tasks_ongoing; }
            set { _tasks_ongoing = value; }
        }

        public Windows.UI.Xaml.Media.Imaging.BitmapImage Avatar { get; set; }

        #region FormatedStrings
        private string logoDateFmt;
        private string logoImgFmt;
        #endregion
        public async System.Threading.Tasks.Task LogoUpdate()
        {
            logoDateFmt = "LogoDate_" + User.Id;
            logoImgFmt = "LogoImg_" + User.Id;
            string tmp = SettingsManager.getOption<string>(logoDateFmt);
            DateTime stored = new DateTime();
            if (tmp != null && tmp != "")
                stored = DateTime.Parse(tmp);
            string avtmp = await BytesToImage.GetStoredImage(logoImgFmt);
            if (avtmp == null)
            {
                SettingsManager.setOption(logoDateFmt, DateTime.Now.ToString());
                await getProjectLogo();
            }
        }
        public async System.Threading.Tasks.Task getProjectLogo()
        {
            LogoModel logoMod = null;
            HttpRequestManager api = HttpRequestManager.Instance;
            object[] token = { HttpRequest.User.GetUser().Token, User.Id };
            HttpResponseMessage res = await api.Get(token, "user/getuseravatar");
            if (res.IsSuccessStatusCode)
            {
                logoMod = api.DeserializeJson<LogoModel>(await res.Content.ReadAsStringAsync());
                if (logoMod.Avatar != null)
                {
                    Avatar = BytesToImage.String64ToImage(logoMod.Avatar);
                    await BytesToImage.StoreImage(logoMod.Avatar, logoImgFmt);
                }
            }
            else
            {
                Debug.WriteLine(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
            }
        }

        public async System.Threading.Tasks.Task SetLogo()
        {
            string tmp = await BytesToImage.GetStoredImage(logoImgFmt);
            if (tmp == null)
            {
                RandomAccessStreamReference rasr = RandomAccessStreamReference.CreateFromUri(new Uri("ms-appx:///Assets/user.png"));
                var streamWithContent = await rasr.OpenReadAsync();
                byte[] buffer = new byte[streamWithContent.Size];
                await streamWithContent.ReadAsync(buffer.AsBuffer(), (uint)streamWithContent.Size, InputStreamOptions.None);
                StorageFile imageFile = await ApplicationData.Current.LocalFolder.CreateFileAsync(logoImgFmt + ".txt", CreationCollisionOption.ReplaceExisting);
                await FileIO.WriteBytesAsync(imageFile, buffer);
            }
            Avatar = tmp == null ? BytesToImage.GetDefaultLogo() : BytesToImage.String64ToImage(tmp);
        }
    }
}
