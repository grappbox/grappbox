using GrappBox.ApiCom;
using GrappBox.Model;
using GrappBox.Ressources;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Diagnostics;
using System.IO;
using System.Linq;
using System.Net.Http;
using System.Text;
using System.Threading.Tasks;
using Windows.ApplicationModel.Activation;
using Windows.ApplicationModel.Core;
using Windows.Networking.BackgroundTransfer;
using Windows.Storage;
using Windows.Storage.Pickers;
using Windows.UI.Popups;
using Windows.UI.Xaml.Controls;

namespace GrappBox.ViewModel
{
    class CloudViewModel : ViewModelBase
    {
        static private CloudViewModel instance = null;
        private const int _chunkSize = 1048576;
        private int _currentChunk = 0;
        private int _chunkNumber;
        private ObservableCollection<CloudModel> _listFiles;
        private CloudModel _fileSelect;
        private string _path;
        private string _dirName;
        private int _streamId;
        private string _fileData;
        public List<string> FullPath = new List<string>();
        private string _dowloadUrl;
        private CoreApplicationView _view;

        static public CloudViewModel GetViewModel()
        {
            if (instance != null)
                return instance;
            else
                return new CloudViewModel();
        }
        public CloudViewModel()
        {
            _view = CoreApplication.GetCurrentView();
            instance = this;
        }

        private void transformFullPathToPath()
        {
            _path = null;
            _path = ",";
            if (FullPath != null)
            {
                foreach (string item in FullPath)
                {
                    _path += item + ',';
                }
                _path.Remove(_path.Count() - 1);
            }
        }

        private void transformFullPathToPathWithSlash()
        {
            _path = "/";
            if (FullPath != null)
            {
                foreach (string item in FullPath)
                {
                    _path += item + '/';
                }
                _path.Remove(_path.Count() - 1);
            }
        }

        #region API
        #region GET
        public async System.Threading.Tasks.Task getLS(string passwordSafe = "titi")
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            transformFullPathToPath();
            object[] token = { User.GetUser().Token, SettingsManager.getOption<int>("ProjectIdChoosen"), _path };
            HttpResponseMessage res;
            if (passwordSafe != null)
            {
                object[] token2 = { User.GetUser().Token, SettingsManager.getOption<int>("ProjectIdChoosen"), _path, passwordSafe };
                res = await api.Get(token2, "cloud/list");
            }
            else
            {
                res = await api.Get(token, "cloud/list");
            }
            if (res.IsSuccessStatusCode)
            {
                _listFiles = api.DeserializeArrayJson<ObservableCollection<CloudModel>>(await res.Content.ReadAsStringAsync());
                NotifyPropertyChanged("ListFiles");
            }
            else {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        public async System.Threading.Tasks.Task downloadFile(string passwordSafe = null)
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            transformFullPathToPath();
            object[] token = { _path, User.GetUser().Token, SettingsManager.getOption<int>("ProjectIdChoosen") };
            HttpResponseMessage res;
            if (passwordSafe != null)
            {
                object[] token2 = { User.GetUser().Token, SettingsManager.getOption<int>("ProjectIdChoosen"), _path, passwordSafe };
                res = await api.Get(token2, "cloud/file");
            }
            else
            {
                res = await api.Get(token, "cloud/file");
            }
            if (res.StatusCode == System.Net.HttpStatusCode.Redirect)
            {
                _dowloadUrl = res.Headers.Location.AbsoluteUri;
                FolderPicker folderPicker = new FolderPicker();
                folderPicker.SuggestedStartLocation = PickerLocationId.Downloads;
                _view.Activated += folderActivated;
                folderPicker.PickFolderAndContinue();
            }
            else {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        private async void folderActivated(CoreApplicationView sender, IActivatedEventArgs args1)
        {
            FolderPickerContinuationEventArgs args = args1 as FolderPickerContinuationEventArgs;

            if (args != null)
            {
                if (args.Folder == null) return;

                _view.Activated -= folderActivated;
                using (HttpClient httpClient = new HttpClient())
                {
                    var data = await httpClient.GetByteArrayAsync(new Uri(_dowloadUrl, UriKind.Absolute));
                    var file = await args.Folder.CreateFileAsync(_fileSelect.Filename, CreationCollisionOption.ReplaceExisting);
                    using (var targetStream = await file.OpenAsync(FileAccessMode.ReadWrite))
                    {
                        await targetStream.AsStreamForWrite().WriteAsync(data, 0, data.Length);
                        await targetStream.FlushAsync();
                    }

                    ContentDialog cd = new ContentDialog();
                    cd.Title = "Success";
                    cd.Content = "Download completed";
                    cd.HorizontalContentAlignment = Windows.UI.Xaml.HorizontalAlignment.Center;
                    cd.VerticalContentAlignment = Windows.UI.Xaml.VerticalAlignment.Center;
                    var t = cd.ShowAsync();
                    await System.Threading.Tasks.Task.Delay(TimeSpan.FromSeconds(1.5));
                    t.Cancel();
                }
            }
        }

        public async System.Threading.Tasks.Task downloadFileSecure(string password, string passwordSafe = null)
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            transformFullPathToPath();
            object[] token = { _path, User.GetUser().Token, SettingsManager.getOption<int>("ProjectIdChoosen"), password };
            HttpResponseMessage res;
            if (passwordSafe != null)
            {
                object[] token2 = { User.GetUser().Token, SettingsManager.getOption<int>("ProjectIdChoosen"), _path, password, passwordSafe };
                res = await api.Get(token2, "cloud/filesecured");
            }
            else
            {
                res = await api.Get(token, "cloud/filesecured");
            }
            if (res.IsSuccessStatusCode)
            {
            }
            else {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }
        #endregion GET

        #region POST
        public async System.Threading.Tasks.Task createDir(string passwordSafe = null)
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            Dictionary<string, object> props = new Dictionary<string, object>();
            transformFullPathToPathWithSlash();

            props.Add("token", User.GetUser().Token);
            props.Add("project_id", SettingsManager.getOption<int>("ProjectIdChoosen"));
            props.Add("path", _path);
            props.Add("dir_name", _dirName);
            if (passwordSafe != null)
                props.Add("passwordSafe ", passwordSafe);
            HttpResponseMessage res = await api.Post(props, "cloud/createdir");
            if (res.IsSuccessStatusCode)
            {
                await getLS(passwordSafe);
            }
            else {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
            props.Clear();
        }

        public async System.Threading.Tasks.Task uploadFile(string fileName, string password = null, string passwordSafe = null)
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            Dictionary<string, object> props = new Dictionary<string, object>();
            transformFullPathToPathWithSlash();

            props.Add("filename", fileName);
            props.Add("project_id", SettingsManager.getOption<int>("ProjectIdChoosen"));
            props.Add("path", _path);
            if (password != null)
                props.Add("password  ", password);
            HttpResponseMessage res;
            if (passwordSafe != null)
            {
                res = await api.Post(props, "cloud/stream/" + User.GetUser().Token + "/" + SettingsManager.getOption<int>("ProjectIdChoosen") + "/" + passwordSafe);
            }
            else
            {
                res = await api.Post(props, "cloud/stream/" + User.GetUser().Token + "/" + SettingsManager.getOption<int>("ProjectIdChoosen"));
            }
            if (res.IsSuccessStatusCode)
            {
                CloudModel tmp = api.DeserializeJson<CloudModel>(await res.Content.ReadAsStringAsync());
                _streamId = tmp.streamId;
                _chunkNumber = _fileData.Length / _chunkSize;
                if (_fileData.Length != _chunkNumber * _chunkSize)
                    ++_chunkNumber;
                await upload();
            }
            else {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
            props.Clear();
        }
        #endregion POST

        #region PUT
        private async System.Threading.Tasks.Task upload()
        {
            if (_currentChunk < _chunkNumber && _fileData!= null)
            {
                ApiCommunication api = ApiCommunication.GetInstance();
                Dictionary<string, object> props = new Dictionary<string, object>();
                string data = null;

                props.Add("token", User.GetUser().Token);
                props.Add("project_id", SettingsManager.getOption<int>("ProjectIdChoosen"));
                props.Add("stream_id", _streamId);
                props.Add("chunk_numbers", _chunkNumber);
                props.Add("current_chunk", _currentChunk);
                if (_fileData.Length < _chunkSize)
                {
                    data = _fileData;
                    _fileData = null;
                }
                else
                {
                    data = _fileData.Substring(0, _chunkSize);
                    _fileData = _fileData.Remove(0, _chunkSize);
                }
                props.Add("file_chunk", data);
                HttpResponseMessage res = await api.Put(props, "cloud/file");
                if (res.StatusCode == System.Net.HttpStatusCode.OK)
                {
                    ContentDialog cd = new ContentDialog();
                    cd.Title = "Success";
                    cd.Content = "Upload: " + ((float)_currentChunk / (float)_chunkNumber * 100) + "%";
                    cd.HorizontalContentAlignment = Windows.UI.Xaml.HorizontalAlignment.Center;
                    cd.VerticalContentAlignment = Windows.UI.Xaml.VerticalAlignment.Center;
                    var t = cd.ShowAsync();
                    await System.Threading.Tasks.Task.Delay(TimeSpan.FromSeconds(1.5));
                    t.Cancel();

                    ++_currentChunk;
                    await upload();
                }
                else {
                    MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                    await msgbox.ShowAsync();
                }
                props.Clear();
            }
            else
            {
                ContentDialog cd = new ContentDialog();
                cd.Title = "Success";
                cd.Content = "Upload complete!";
                cd.HorizontalContentAlignment = Windows.UI.Xaml.HorizontalAlignment.Center;
                cd.VerticalContentAlignment = Windows.UI.Xaml.VerticalAlignment.Center;
                var t = cd.ShowAsync();
                await System.Threading.Tasks.Task.Delay(TimeSpan.FromSeconds(1.5));
                t.Cancel();

                await closeStream();
            }
        }
        #endregion PUT

        #region DELETE
        public async System.Threading.Tasks.Task closeStream()
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            object[] token = { User.GetUser().Token, SettingsManager.getOption<int>("ProjectIdChoosen"), _streamId };
            HttpResponseMessage res = await api.Delete(token, "cloud/stream");
            if (res.IsSuccessStatusCode)
            {
                _currentChunk = 0;
                _chunkNumber = 0;
                _fileData = null;
                _streamId = 0;
                await getLS();
            }
            else {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        public async System.Threading.Tasks.Task deleteFile(string password = null, string passwordSafe = null)
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            transformFullPathToPath();
            List<object> token = new List<object>();
            token.Add(User.GetUser().Token);
            token.Add(SettingsManager.getOption<int>("ProjectIdChoosen"));
            token.Add(_path);
            HttpResponseMessage res;
            if (password != null)
            {
                token.Add(password);
                if (passwordSafe != null)
                    token.Add(passwordSafe);
                res = await api.Delete(token.ToArray(), "cloud/filesecured");
            }
            else
            {
                if (passwordSafe != null)
                    token.Add(passwordSafe);
                res = await api.Delete(token.ToArray(), "cloud/file");
            }
            if (res.IsSuccessStatusCode)
            {
                FullPath.Remove(FullPath.ElementAt(FullPath.Count() - 1));
                await getLS(passwordSafe);
            }
            else {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }
        #endregion DELETE
        #endregion API

        public CloudModel FileSelect
        {
            get { return _fileSelect; }
            set { _fileSelect = value; }
        }

        public string FolderName
        {
            get { return _dirName; }
            set { _dirName = value; }
        }

        public string FileData
        {
            get { return _fileData; }
            set { _fileData = value; }
        }

        public ObservableCollection<CloudModel> ListFiles
        {
            get { return _listFiles; }
        }
    }
}
