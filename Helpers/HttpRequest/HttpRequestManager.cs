using GrappBox.Helpers;

using GrappBox.Helpers;

using Newtonsoft.Json;
using Newtonsoft.Json.Linq;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Text;
using System.Threading.Tasks;
using Windows.Web.Http;
using Windows.Web.Http.Headers;

namespace GrappBox.HttpRequest
{
    //This class is a singleton
    internal class HttpRequestManager
    {
        #region Private members

        private const string baseAdress = "https://api.grappbox.com/";
        private const string version = "0.3/";

        private const string baseUrl = baseAdress + version;

        #endregion Private members

        #region Singleton instantiation

        private static volatile HttpRequestManager instance;
        private static object syncRoot = new Object();

        public static HttpRequestManager Instance
        {
            get
            {
                if (instance == null)
                {
                    lock (syncRoot)
                    {
                        if (instance == null)
                            instance = new HttpRequestManager();
                    }
                }

                return instance;
            }
        }

        private HttpClient webclient;

        private HttpRequestManager()
        {
            webclient = new HttpClient();
            webclient.DefaultRequestHeaders.Accept.Clear();
            webclient.DefaultRequestHeaders.Accept.Add(new Windows.Web.Http.Headers.HttpMediaTypeWithQualityHeaderValue("application/json"));
        }

        #endregion Singleton instantiation

        #region Utils

        public Uri RequestUri(string requestUrl)
        {
            Uri reqUri = new Uri(baseUrl + requestUrl);
            return reqUri;
        }

        public static T DeserializeJson<T>(string json)
        {
            string data = JObject.Parse(json).GetValue("data").ToString();
            try
            {
                T obj;
                obj = JsonConvert.DeserializeObject<T>(data);
                return obj;
            }
            catch (Exception ex)
            {
                Debug.WriteLine(ex.Message);
                throw (ex);
            }
        }

        public static T DeserializeArrayJson<T>(string json)
        {
            string data = JObject.Parse(json).GetValue("data").ToString();
            data = JObject.Parse(data).GetValue("array").ToString();
            try
            {
                T obj;
                obj = JsonConvert.DeserializeObject<T>(data);
                return obj;
            }
            catch (Exception ex)
            {
                Debug.WriteLine(ex.Message);
                throw (ex);
            }
        }

        public string GetErrorMessage(string jsonTxt)
        {
            if (jsonTxt == "")
                return ("No internet connection");
            string message = "Undeterminate Error";
            try
            {
                JObject info = (JObject)JObject.Parse(jsonTxt).GetValue("info");
                message = info.GetValue("return_message").ToString();
                string[] split = message.Split('-');
                message = split[2];
            }
            catch (Exception ex)
            {
                return ex.Message;
            }
            return message;
        }

        #endregion Utils

        #region Requests

        public async Task<HttpResponseMessage> Post(Dictionary<string, object> properties, string url)
        {
            JObject post = new JObject();
            JObject data = new JObject();
            foreach (KeyValuePair<string, object> it in properties)
            {
                data.Add(it.Key, JToken.FromObject(it.Value));
            }
            post.Add("data", JToken.FromObject(data));
            Debug.WriteLine(post.ToString());
            HttpStringContent sc = new HttpStringContent(post.ToString());
            HttpResponseMessage res = null;
            try
            {
                res = await webclient.PostAsync(RequestUri(url), sc);
            }
            catch (Exception ex)
            {
                Debug.WriteLine(ex.Message);
                return null;
            }
            return res;
        }

        public async Task<bool> Login(string username, string password)
        {
            JObject post = new JObject();
            JObject data = new JObject();
            data.Add("login", JToken.FromObject(username));
            data.Add("password", JToken.FromObject(password));
            data.Add("mac", SystemInformation.GetUniqueIdentifier());
            data.Add("flag", JToken.FromObject("wph"));
            data.Add("is_client", JToken.FromObject(false));
            data.Add("device_name", JToken.FromObject("WindowsPhone"));

            post.Add("data", JToken.FromObject(data));
            Debug.WriteLine(post.ToString());
            HttpStringContent sc = new HttpStringContent(post.ToString());
            HttpResponseMessage res = null;
            Debug.WriteLine(RequestUri("account/login"));
            try
            {
                res = await webclient.PostAsync(RequestUri("account/login"), sc);
            }
            catch (Exception ex)
            {
                Debug.WriteLine(ex.Message);
                return false;
            }
            if (res.IsSuccessStatusCode)
            {
                try
                {
                    AppGlobalHelper.CurrentUser = HttpRequestManager.DeserializeJson<User>(await res.Content.ReadAsStringAsync());
                }
                catch (Exception ex)
                {
                    Debug.WriteLine(ex.Message);
                    return false;
                }
                string token = AppGlobalHelper.CurrentUser.Token;
                Debug.WriteLine(token);
                webclient.DefaultRequestHeaders.Authorization = new HttpCredentialsHeaderValue(token);
                return true;
            }
            else
                return false;
        }

        public async Task<HttpResponseMessage> Put(Dictionary<string, object> properties, string url)
        {
            JObject put = new JObject();
            JObject data = new JObject();
            foreach (KeyValuePair<string, object> it in properties)
            {
                data.Add(it.Key, JToken.FromObject(it.Value));
            }
            put.Add("data", JToken.FromObject(data));
            HttpStringContent sc = new HttpStringContent(put.ToString(), Windows.Storage.Streams.UnicodeEncoding.Utf8, "application/json");
            HttpResponseMessage res = null;
            try
            {
                res = await webclient.PutAsync(RequestUri(url), sc);
            }
            catch (Exception ex)
            {
                Debug.WriteLine(ex.Message);
                return null;
            }
            return res;
        }

        public async Task<HttpResponseMessage> Get(object[] values, string url)
        {
            HttpResponseMessage res = null;
            try
            {
                StringBuilder get = new StringBuilder("");
                if (values != null)
                {
                    get.Append("/");
                    for (int i = 0; i < values.Length; ++i)
                    {
                        get.Append(values[i]);
                        if (i + 1 < values.Length)
                            get.Append("/");
                    }
                }
                Debug.WriteLine(RequestUri(url + get));
                res = await webclient.GetAsync(RequestUri(url + get));
            }
            catch (Exception ex)
            {
                Debug.WriteLine(ex.Message);
                return null;
            }
            return res;
        }

        public async Task<HttpResponseMessage> Delete(object[] values, string url)
        {
            HttpResponseMessage res = null;
            try
            {
                StringBuilder del = new StringBuilder("/");
                for (int i = 0; i < values.Length; ++i)
                {
                    del.Append(values[i]);
                    if (i + 1 < values.Length)
                        del.Append("/");
                }
                res = await webclient.DeleteAsync(RequestUri(url + del));
            }
            catch (Exception ex)
            {
                Debug.WriteLine(ex.Message);
                return null;
            }
            return res;
        }

        #endregion Requests
    }
}