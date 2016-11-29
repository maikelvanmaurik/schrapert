module Jekyll
  module SidebarItemFilter
    def docs_sidebar_link(item)
      return sidebar_helper(item, 'docs')
    end

    def community_sidebar_link(item)
      return sidebar_helper(item, 'community')
    end

    def tutorial_sidebar_link(item)
      return sidebar_helper(item, 'tutorial')
    end

    def sidebar_helper(item, group)
      forceInternal = item["forceInternal"]

      pageID = @context.registers[:page]["id"]
      baseUrl = @context.registers[:site].baseurl
      itemID = item["id"]
      href = item["href"] || "#{baseUrl}/#{group}/#{itemID}.html"
      classes = []
      if pageID == itemID
        classes.push("active")
      end
      if item["href"] && (forceInternal == nil)
        classes.push("external")
      end
      className = classes.size > 0  ? " class=\"#{classes.join(' ')}\"" : ""


      return "<a href=\"#{href}\"#{className}>#{item["title"]}</a>"
    end

  end
end

Liquid::Template.register_filter(Jekyll::SidebarItemFilter)