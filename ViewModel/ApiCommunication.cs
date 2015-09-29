using System;
using System.Collections.Generic;
using System.Linq;
using System.Net.Http;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox.ViewModel
{
    //This class is a singleton
    class ApiCommunication
    {
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
        }
    }
}
