require 'liquid'

module WidontFilter

  # Return the element's text after applying the filter
  def widont(text)
    text.gsub(/ ([^ ]+)$/, '&nbsp;\1')
  end

end

Liquid::Template.register_filter(WidontFilter)
