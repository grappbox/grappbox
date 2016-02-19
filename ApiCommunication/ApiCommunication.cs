using Newtonsoft.Json;
using Newtonsoft.Json.Linq;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Linq;
using System.Net.Http;
using System.Net.Http.Headers;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox.ApiCom
{
    //This class is a singleton
    class ApiCommunication
    {
        private string version = "V0.9";
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
            webclient.BaseAddress = new Uri("http://api.grappbox.com/app_dev.php/" + version + "/");
            webclient.DefaultRequestHeaders.Accept.Clear();
            webclient.DefaultRequestHeaders.Accept.Add(new MediaTypeWithQualityHeaderValue("application/json"));
            webclient.Timeout = new TimeSpan(0, 0, 20);
            Debug.WriteLine(webclient.BaseAddress);
        }
        public async Task<string> Request(string requestUrl)
        {
            HttpResponseMessage result = await webclient.GetAsync(requestUrl);
            return result.Content.ToString();
        }
        public async Task<HttpResponseMessage> Login(string username, string password)
        {
            User user = null;
            JObject post =
             new JObject(
                 new JProperty("login", username),
                 new JProperty("password", password));
            StringContent sc = new StringContent(post.ToString(), null, "application/json");
            HttpResponseMessage res = await webclient.PostAsync("accountadministration/login", sc);
            if (res.IsSuccessStatusCode)
            {
                string json = await res.Content.ReadAsStringAsync();
                json = json.Remove(0, 8);
                json = json.Remove(json.Length - 1, 1);
                Debug.WriteLine(json);
                user = JsonConvert.DeserializeObject<User>(json);
                Debug.WriteLine("user " + user.Lastname);
            }
            return res;
        }
        public async Task<HttpResponseMessage> Post(string[] properties, object[] values, string url)
        {
            JObject post = new JObject();
            for (int i = 0; i < properties.Length; ++i)
            {
                post.Add(properties[i], JToken.FromObject(values[i]));
            }
            Debug.WriteLine(post.ToString());
            StringContent sc = new StringContent(post.ToString(), null, "application/json");
            HttpResponseMessage res = await webclient.PostAsync(url, sc);
            if (res.IsSuccessStatusCode)
            {
                string json = await res.Content.ReadAsStringAsync();
                Debug.WriteLine(json);
            }
            return res;
        }
        public async Task<HttpResponseMessage> Get(object[] values, string url)
        {
            StringBuilder get = new StringBuilder();
            for (int i = 0; i < values.Length; ++i)
            {
                get.Append(values[i]);
                if (i + 1 < values.Length)
                    get.Append("/");
            }
            Debug.WriteLine(get.ToString());
            HttpResponseMessage res = await webclient.GetAsync(url + get);
            if (res.IsSuccessStatusCode)
            {
                string json = await res.Content.ReadAsStringAsync();
                Debug.WriteLine(json);
            }
            return res;
        }
    }
}