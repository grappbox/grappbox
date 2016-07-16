using Windows.Foundation;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Media;

namespace GrappBox.Model
{
    interface ICustomShape
    {
        void Initialize(Point pos, SolidColorBrush stroke, SolidColorBrush fill, double thickness);
        void Initialize(string txt, bool bold, bool italic, SolidColorBrush stroke, int size);
        void Initialize(PointCollection pos, SolidColorBrush border, SolidColorBrush fill, double thickness);
        void Update(Point p);
        UIElement GetElement();
    }
}
