using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Windows.UI.Popups;

namespace GrappBox.Ressources
{
    public static class ExceptionExtension
    {
        public static Exception Log(this Exception ex, string msg = null, params object[] args)
        {
            if (msg != "")
                Debug.WriteLine(msg, args);
            Debug.WriteLine("Exception_occured: {0}", ex.Message);
            return ex;
        }
        public async static Task<Exception> Display(this Exception ex, string msg = null)
        {
            MessageDialog dlg = new MessageDialog("");
            dlg.Content = msg ?? ex.Message;
            await dlg.ShowAsync();
            return ex;
        }
    }
}
