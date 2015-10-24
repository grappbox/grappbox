using System;
using System.Collections.Generic;
using System.Linq;
using System.Net.Http;
using System.Net.Http.Headers;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox.ApiCommunication
{
    //This class is a singleton
    class ApiCommunication
    {
        public string BaseAdress
        {
            get
            {
                return webclient.BaseAddress.OriginalString;
            }
            set
            {
                webclient.BaseAddress = new Uri(value);
            }
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
            webclient.DefaultRequestHeaders.Accept.Add(
                            new MediaTypeWithQualityHeaderValue("application/json"));
            webclient.BaseAddress = new Uri("localhost:2403/");
        }
        public string Request(string requestUrl)
        {
            HttpResponseMessage result = webclient.GetAsync(requestUrl).Result;
            return result.Content.ToString();
        }
    }
}
