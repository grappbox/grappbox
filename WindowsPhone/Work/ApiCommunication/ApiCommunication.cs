using Newtonsoft.Json;
using Newtonsoft.Json.Linq;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Text;
using System.Threading.Tasks;
using Windows.Web.Http;

namespace GrappBox.ApiCom
{
    //This class is a singleton
    class ApiCommunication
    {
        #region Private members
        private const string baseAdress = "http://api.grappbox.com/app_dev.php/";
        private const string version = "V0.2/";
        private const string baseUrl = baseAdress + version;
        #endregion
        #region Singleton instantiation

        private static volatile ApiCommunication instance;
        private static object syncRoot = new Object();
        public static ApiCommunication Instance
        {
            get
            {
                if (instance == null)
                {
                    lock (syncRoot)
                    {
                        if (instance == null)
                            instance = new ApiCommunication();
                    }
                }

                return instance;
            }
        }
        private HttpClient webclient;
        private ApiCommunication()
        {
            webclient = new HttpClient();
            webclient.DefaultRequestHeaders.Accept.Clear();
            webclient.DefaultRequestHeaders.Accept.Add(new Windows.Web.Http.Headers.HttpMediaTypeWithQualityHeaderValue("application/json"));
        }
        #endregion
        #region Utils
        public Uri RequestUri(string requestUrl)
        {
            Uri reqUri = new Uri(baseUrl + requestUrl);
            return reqUri;
        }
        public T DeserializeJson<T>(string json)
        {
            string data = JObject.Parse(json).GetValue("data").ToString();
            return JsonConvert.DeserializeObject<T>(data);
        }
        public T DeserializeArrayJson<T>(string json)
        {
            string data = JObject.Parse(json).GetValue("data").ToString();
            data = JObject.Parse(data).GetValue("array").ToString();
            return JsonConvert.DeserializeObject<T>(data);
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
        #endregion
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
                StringBuilder get = new StringBuilder("/");
                for (int i = 0; i < values.Length; ++i)
                {
                    get.Append(values[i]);
                    if (i + 1 < values.Length)
                        get.Append("/");
                }
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
        #endregion
    }
}