using Grappbox.Model;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.IO;
using System.Linq;
using Windows.Web.Http;
using System.Text;
using Windows.ApplicationModel.Core;
using Windows.Storage;
using Windows.Storage.Pickers;
using Windows.UI.Popups;
using Windows.UI.Xaml.Controls;
using Grappbox.Helpers;
using Grappbox.HttpRequest;
using Windows.Storage.AccessCache;
using Windows.Networking.BackgroundTransfer;
using System.Threading;
using Windows.Web;
using System.Globalization;
using System.Diagnostics;
using System.Threading.Tasks;

namespace Grappbox.ViewModel
{
    class CloudViewModel : ViewModelBase
    {
        static private CloudViewModel instance = null;
        private const int _chunkSize = 1048576;
        private int _currentChunk = 0;
        private int _chunkNumber;
        private ObservableCollection<CloudModel> _listSecured = new ObservableCollection<CloudModel>();
        private ObservableCollection<CloudModel> _listDir = new ObservableCollection<CloudModel>();
        private ObservableCollection<CloudModel> _listFile = new ObservableCollection<CloudModel>();
        private CloudModel _fileSelect;
        private string _path;
        private string _dirName;
        private int _streamId;
        private string _fileData;
        private string _passwordSafe;
        private string _password;
        public List<string> FullPath = new List<string>();
        private CoreApplicationView _view;
        private CancellationTokenSource _cts;

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
            _cts = new CancellationTokenSource();
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
        public async System.Threading.Tasks.Task getLS()
        {
            if (_fileSelect != null && _fileSelect.Filename == "Safe" && _fileSelect.Type == "dir" && string.IsNullOrEmpty(_passwordSafe))
            {
                _fileSelect = null;
                FullPath.RemoveAt(FullPath.Count - 1);
                return;
            }
            transformFullPathToPath();
            object[] token = { SessionHelper.GetSession().ProjectId, _path };
            HttpResponseMessage res;
            if (!string.IsNullOrEmpty(_passwordSafe))
            {
                object[] token2 = { SessionHelper.GetSession().ProjectId, _path, _passwordSafe };
                res = await HttpRequestManager.Get(token2, "cloud/list");
            }
            else
            {
                res = await HttpRequestManager.Get(token, "cloud/list");
            }
            if (res.IsSuccessStatusCode && res.StatusCode == HttpStatusCode.Ok)
            {
                _listSecured.Clear();
                _listDir.Clear();
                _listFile.Clear();
                var files = SerializationHelper.DeserializeArrayJson<ObservableCollection<CloudModel>>(await res.Content.ReadAsStringAsync());
                foreach (var item in files)
                {
                    if (item.IsSecured)
                    {
                        if (item.Filename == "Safe")
                            _listSecured.Insert(0, item);
                        else
                            _listSecured.Add(item);
                    }
                    else if (item.Type == "dir")
                        _listDir.Add(item);
                    else
                        _listFile.Add(item);
                }
            }
            else
            {
                if (res.StatusCode == HttpStatusCode.PartialContent)
                    _passwordSafe = string.Empty;
                MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        public async System.Threading.Tasks.Task downloadFile()
        {
            transformFullPathToPath();
            object[] token = { _path, SessionHelper.GetSession().ProjectId };
            HttpResponseMessage res;
            if (!string.IsNullOrEmpty(_passwordSafe))
            {
                object[] token2 = { SessionHelper.GetSession().ProjectId, _path, _passwordSafe };
                res = await HttpRequestManager.Get(token2, "cloud/file");
            }
            else
            {
                res = await HttpRequestManager.Get(token, "cloud/file");
            }
            if (res.StatusCode == HttpStatusCode.Ok)
            {
                string dowloadUrl = res.Headers.Location.AbsoluteUri;
                FolderPicker folderPicker = new FolderPicker();
                folderPicker.SuggestedStartLocation = PickerLocationId.Downloads;
                StorageFolder folder = await folderPicker.PickSingleFolderAsync();
                if (folder != null)
                {
                    // Application now has read/write access to all contents in the picked folder (including other sub-folder contents)
                    StorageApplicationPermissions.FutureAccessList.AddOrReplace("PickedFolderToken", folder);
                    Uri source = new Uri(dowloadUrl, UriKind.Absolute);

                    StorageFile destinationFile = await folder.CreateFileAsync(_fileSelect.Filename, CreationCollisionOption.ReplaceExisting);

                    BackgroundDownloader downloader = new BackgroundDownloader();
                    DownloadOperation download = downloader.CreateDownload(source, destinationFile);

                    // Attach progress and completion handlers.
                    await HandleDownloadAsync(download);

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
            else
            {
                MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }

            FullPath.RemoveAt(FullPath.Count - 1);
        }

        public async System.Threading.Tasks.Task downloadFileSecure()
        {
            transformFullPathToPath();
            object[] token = { _path, SessionHelper.GetSession().ProjectId, _password };
            HttpResponseMessage res;
            if (!string.IsNullOrEmpty(_passwordSafe))
            {
                object[] token2 = { SessionHelper.GetSession().ProjectId, _path, _password, _passwordSafe };
                res = await HttpRequestManager.Get(token2, "cloud/filesecured");
            }
            else
            {
                res = await HttpRequestManager.Get(token, "cloud/filesecured");
            }
            if (res.IsSuccessStatusCode)
            {
                string dowloadUrl = res.Headers.Location.AbsoluteUri;
                FolderPicker folderPicker = new FolderPicker();
                folderPicker.SuggestedStartLocation = PickerLocationId.Downloads;
                StorageFolder folder = await folderPicker.PickSingleFolderAsync();
                if (folder != null)
                {
                    // Application now has read/write access to all contents in the picked folder (including other sub-folder contents)
                    StorageApplicationPermissions.FutureAccessList.AddOrReplace("PickedFolderToken", folder);
                    Uri source = new Uri(dowloadUrl, UriKind.Absolute);

                    StorageFile destinationFile = await folder.CreateFileAsync(_fileSelect.Filename, CreationCollisionOption.ReplaceExisting);

                    BackgroundDownloader downloader = new BackgroundDownloader();
                    DownloadOperation download = downloader.CreateDownload(source, destinationFile);

                    // Attach progress and completion handlers.
                    await HandleDownloadAsync(download);

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
            else
            {
                MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }

            FullPath.RemoveAt(FullPath.Count - 1);
        }
        #endregion GET

        #region POST
        public async System.Threading.Tasks.Task<bool> createDir()
        {
            Dictionary<string, object> props = new Dictionary<string, object>();
            transformFullPathToPathWithSlash();

            props.Add("project_id", SessionHelper.GetSession().ProjectId);
            props.Add("path", _path);
            props.Add("dir_name", _dirName);
            if (!string.IsNullOrEmpty(_passwordSafe))
                props.Add("passwordSafe ", _passwordSafe);
            HttpResponseMessage res = await HttpRequestManager.Post(props, "cloud/createdir");
            if (res.IsSuccessStatusCode)
            {
                await getLS();
                return true;
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
            props.Clear();
            return false;
        }

        public async System.Threading.Tasks.Task uploadFile(string fileName)
        {
            Dictionary<string, object> props = new Dictionary<string, object>();
            transformFullPathToPathWithSlash();

            props.Add("filename", fileName);
            props.Add("project_id", SessionHelper.GetSession().ProjectId);
            props.Add("path", _path);
            if (!string.IsNullOrEmpty(_password))
                props.Add("password  ", _password);
            HttpResponseMessage res;
            if (!string.IsNullOrEmpty(_passwordSafe))
            {
                res = await HttpRequestManager.Post(props, "cloud/stream/" + SessionHelper.GetSession().ProjectId + "/" + _passwordSafe);
            }
            else
            {
                res = await HttpRequestManager.Post(props, "cloud/stream/" + SessionHelper.GetSession().ProjectId);
            }
            if (res.IsSuccessStatusCode)
            {
                CloudModel tmp = SerializationHelper.DeserializeJson<CloudModel>(await res.Content.ReadAsStringAsync());
                _streamId = tmp.StreamId;
                _chunkNumber = _fileData.Length / _chunkSize;
                if (_fileData.Length != _chunkNumber * _chunkSize)
                    ++_chunkNumber;
                await upload();
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
            props.Clear();
        }
        #endregion POST

        #region PUT
        private async System.Threading.Tasks.Task upload()
        {
            if (_currentChunk < _chunkNumber && _fileData != null)
            {
                Dictionary<string, object> props = new Dictionary<string, object>();
                string data = null;

                props.Add("project_id", SessionHelper.GetSession().ProjectId);
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
                HttpResponseMessage res = await HttpRequestManager.Put(props, "cloud/file");
                if (res.IsSuccessStatusCode == true)
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
                else
                {
                    MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync()));
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
            object[] token = { SessionHelper.GetSession().ProjectId, _streamId };
            HttpResponseMessage res = await HttpRequestManager.Delete(token, "cloud/stream");
            if (res.IsSuccessStatusCode)
            {
                _currentChunk = 0;
                _chunkNumber = 0;
                _fileData = null;
                _streamId = 0;
                await getLS();
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
        }

        public async System.Threading.Tasks.Task deleteFile()
        {
            transformFullPathToPath();
            List<object> token = new List<object>();
            token.Add(SessionHelper.GetSession().ProjectId);
            token.Add(_path);
            HttpResponseMessage res;
            if (!string.IsNullOrEmpty(_password))
            {
                token.Add(_password);
                if (!string.IsNullOrEmpty(_passwordSafe))
                    token.Add(_passwordSafe);
                res = await HttpRequestManager.Delete(token.ToArray(), "cloud/filesecured");
            }
            else
            {
                if (!string.IsNullOrEmpty(_passwordSafe))
                    token.Add(_passwordSafe);
                res = await HttpRequestManager.Delete(token.ToArray(), "cloud/file");
            }
            if (res.IsSuccessStatusCode)
            {
                FullPath.Remove(FullPath.ElementAt(FullPath.Count() - 1));
                await getLS();
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(HttpRequestManager.GetErrorMessage(await res.Content.ReadAsStringAsync()));
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

        public string PasswordSafe
        {
            get { return _passwordSafe; }
            set { _passwordSafe = value; }
        }
        public string Password
        {
            get { return _password; }
            set { _password = value; }
        }

        public string FileData
        {
            get { return _fileData; }
            set { _fileData = value; }
        }

        public ObservableCollection<CloudModel> ListSecured
        {
            get { return _listSecured; }
        }
        public ObservableCollection<CloudModel> ListDir
        {
            get { return _listDir; }
        }
        public ObservableCollection<CloudModel> ListFile
        {
            get { return _listFile; }
        }

        #region Download functions
        private async System.Threading.Tasks.Task HandleDownloadAsync(DownloadOperation download)
        {
            try
            {
                Progress<DownloadOperation> progressCallback = new Progress<DownloadOperation>(DownloadProgress);

                // Start the download and attach a progress handler.
                await download.StartAsync().AsTask(_cts.Token, progressCallback);

                ResponseInformation response = download.GetResponseInformation();

                // GetResponseInformation() returns null for non-HTTP transfers (e.g., FTP).
                string statusCode = response != null ? response.StatusCode.ToString() : String.Empty;
            }
            catch (TaskCanceledException)
            {
                Debug.WriteLine("Canceled: " + download.Guid);
            }
            catch (Exception ex)
            {
                if (!IsExceptionHandled("Execution error", ex, download))
                {
                    throw;
                }
            }
            
        }

        // Note that this event is invoked on a background thread, so we cannot access the UI directly.
        private void DownloadProgress(DownloadOperation download)
        {
            // DownloadOperation.Progress is updated in real-time while the operation is ongoing. Therefore,
            // we must make a local copy so that we can have a consistent view of that ever-changing state
            // throughout this method's lifetime.
            BackgroundDownloadProgress currentProgress = download.Progress;


            double percent = 100;
            if (currentProgress.TotalBytesToReceive > 0)
            {
                percent = currentProgress.BytesReceived * 100 / currentProgress.TotalBytesToReceive;
            }

            if (currentProgress.HasResponseChanged)
            {
                // We have received new response headers from the server.
                // Be aware that GetResponseInformation() returns null for non-HTTP transfers (e.g., FTP).
                ResponseInformation response = download.GetResponseInformation();
                int headersCount = response != null ? response.Headers.Count : 0;


                // If you want to stream the response data this is a good time to start.
                // download.GetResultStreamAt(0);
            }
        }
        private bool IsExceptionHandled(string title, Exception ex, DownloadOperation download = null)
        {
            WebErrorStatus error = BackgroundTransferError.GetStatus(ex.HResult);
            if (error == WebErrorStatus.Unknown)
            {
                return false;
            }

            if (download == null)
            {
                Debug.WriteLine(String.Format(CultureInfo.CurrentCulture, "Error: {0}: {1}", title, error));
            }
            else
            {
                Debug.WriteLine(String.Format(CultureInfo.CurrentCulture, "Error: {0} - {1}: {2}", download.Guid, title, error));
            }

            return true;
        }
        #endregion
    }
}
