using Newtonsoft.Json;
using Newtonsoft.Json.Linq;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Grappbox.Helpers
{
    class SerializationHelper
    {
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
        public static T DeserializeJson<T>(string json, JsonSerializerSettings settings)
        {
            string data = JObject.Parse(json).GetValue("data").ToString();
            try
            {
                T obj;
                obj = JsonConvert.DeserializeObject<T>(data, settings);
                return obj;
            }
            catch (Exception ex)
            {
                Debug.WriteLine(ex.Message);
                throw (ex);
            }
        }

        public static T DeserializeObject<T>(string data)
        {
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
        public static T DeserializeArrayJson<T>(string json, JsonSerializerSettings settings)
        {
            string data = JObject.Parse(json).GetValue("data").ToString();
            data = JObject.Parse(data).GetValue("array").ToString();
            try
            {
                T obj;
                obj = JsonConvert.DeserializeObject<T>(data, settings);
                return obj;
            }
            catch (Exception ex)
            {
                Debug.WriteLine(ex.Message);
                throw (ex);
            }
        }
    }
}
