using Newtonsoft.Json;
using Newtonsoft.Json.Linq;
using System;
using System.Collections.Generic;
using System.Net.Http;
using System.Net.Http.Headers;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox.ApiCom
{
    //This class is a singleton
    class ApiCommunication
    {
        private string version = "V0.2";
        private static ApiCommunication instance = null;
        public static ApiCommunication GetInstance()
        {
            //This ternary condition return the instance of the Singleton
            return instance == null ? new ApiCommunication() : instance;
        }
        private HttpClient webclient;
        private ApiCommunication()
        {
            HttpClientHandler handler = new HttpClientHandler();
            handler.AllowAutoRedirect = false;
            webclient = new HttpClient(handler);
            webclient.BaseAddress = new Uri("http://api.grappbox.com/app_dev.php/" + version + "/");
            webclient.DefaultRequestHeaders.Accept.Clear();
            webclient.DefaultRequestHeaders.Accept.Add(new MediaTypeWithQualityHeaderValue("application/json"));
            webclient.Timeout = new TimeSpan(0, 0, 20);
        }
        public async Task<string> Request(string requestUrl)
        {
            HttpResponseMessage result = await webclient.GetAsync(requestUrl);
            return result.Content.ToString();
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
            StringContent sc = new StringContent(post.ToString(), null, "application/json");
            HttpResponseMessage res = await webclient.PostAsync(url, sc);
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
            StringContent sc = new StringContent(put.ToString(), null, "application/json");
            HttpResponseMessage res = await webclient.PutAsync(url, sc);
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
            HttpResponseMessage res = await webclient.GetAsync(url + get);
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
            HttpResponseMessage res = await webclient.DeleteAsync(url + del);
            return res;
        }
        public string GetErrorMessage(string jsonTxt)
        {
            JObject info = (JObject)JObject.Parse(jsonTxt).GetValue("info");
            string message = info.GetValue("return_message").ToString();
            string[] split = message.Split('-');
            return split[2];
        }
    }
}