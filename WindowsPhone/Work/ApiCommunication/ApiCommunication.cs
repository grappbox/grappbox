using Newtonsoft.Json;
using Newtonsoft.Json.Linq;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Text;
using System.Threading.Tasks;
using Windows.UI.ViewManagement;
using Windows.Web.Http;
using Windows.Web.Http.Filters;

namespace GrappBox.ApiCom
{
    //This class is a singleton
    class ApiCommunication
    {
        private const string baseAdress = "http://api.grappbox.com/app_dev.php/";
        private const string version = "V0.2/";
        private const string baseUrl = baseAdress + version;
        public Uri RequestUri(string requestUrl)
        {
            Uri reqUri = new Uri(baseUrl + requestUrl);
            return reqUri;
        }
        private static ApiCommunication instance = null;
        public static ApiCommunication GetInstance()
        {
            //This ternary condition return the instance of the Singleton
            return instance == null ? new ApiCommunication() : instance;
        }
        private HttpClient webclient;
        private ApiCommunication()
        {
            webclient = new HttpClient();
            webclient.DefaultRequestHeaders.Accept.Clear();
            webclient.DefaultRequestHeaders.Accept.Add(new Windows.Web.Http.Headers.HttpMediaTypeWithQualityHeaderValue("application/json"));
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
            Windows.Web.Http.HttpResponseMessage res = null;
            try {
                 res = await webclient.PostAsync(RequestUri(url), sc);
            }
            catch (Exception e)
            {
                Debug.WriteLine(e.Message);
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
            Debug.WriteLine("JsonContent {0}", await sc.ReadAsStringAsync());
            HttpResponseMessage res = await webclient.PutAsync(RequestUri(url), sc);
            return res;
        }
        public async Task<HttpResponseMessage> Get(object[] values, string url)
        {
            StringBuilder get = new StringBuilder("/");
            for (int i = 0; i < values.Length; ++i)
            {
                get.Append(values[i]);
                if (i + 1 < values.Length)
                    get.Append("/");
            }
            HttpResponseMessage res = await webclient.GetAsync(RequestUri(url + get));
            return res;
        }
        public async Task<HttpResponseMessage> Delete(object[] values, string url)
        {
            StringBuilder del = new StringBuilder("/");
            for (int i = 0; i < values.Length; ++i)
            {
                del.Append(values[i]);
                if (i + 1 < values.Length)
                    del.Append("/");
            }
            HttpResponseMessage res = await webclient.DeleteAsync(RequestUri(url + del));
            return res;
        }
        public string GetErrorMessage(string jsonTxt)
        {
            if (jsonTxt == "")
                return ("no internet connection");
            JObject info = (JObject)JObject.Parse(jsonTxt).GetValue("info");
            string message = info.GetValue("return_message").ToString();
            string[] split = message.Split('-');
            return split[2];
        }
    }
}